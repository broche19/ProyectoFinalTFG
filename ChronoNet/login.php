<?php
session_start();

include 'Conexion.php';

// Verificar si se enviaron datos por POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['usuario']) && isset($_POST['contrasena'])) {
    // Obtener datos del formulario
    $usuario = trim($_POST['usuario']);
    $contrasena = trim($_POST['contrasena']);

    // Validar datos
    if (empty($usuario) || empty($contrasena)) {
        $_SESSION['error_message'] = "Usuario o contraseña vacíos";
        header("Location: index.php");
        exit();
    }

    try {
        // Realizar consulta para verificar la existencia del usuario y la contraseña
        $query = "SELECT * FROM TUsuarios WHERE Login = :usuario AND Codigo = :contrasena";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $stmt->bindParam(':contrasena', $contrasena, PDO::PARAM_STR);
        $stmt->execute();

        // Procesar resultados
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar si las credenciales son válidas
        if ($data) {
            // Verificar si el usuario es administrador
            if ($data['AdminBool'] == 1) {
                // Inicio de sesión exitoso, almacenar información del usuario en la sesión
                $_SESSION['usuario'] = array(
                    'id' => $data['idUsuario'],
                    'nombre' => $data['Nombre'],
                    'apellidos' => $data['Apellidos'],
                    'admin' => true
                );

                // Redirigir al listado de usuarios (DatoUsuarios.php)
                header("Location: Usuarios.php");
                exit();
            } else {
                // Usuario válido pero no administrador
                $_SESSION['error_message'] = "Credenciales inválidas";
                header("Location: index.php");
                exit();
            }
        } else {
            // Credenciales inválidas
            $_SESSION['error_message'] = "Credenciales inválidas";
            header("Location: index.php");
            exit();
        }
    } catch (PDOException $e) {
        // Manejar errores de consulta
        $_SESSION['error_message'] = "Error en la base de datos: " . $e->getMessage();
        header("Location: index.php");
        exit();
    }
} else {
    // Si no se enviaron datos por POST, volver al login
    header("Location: index.php");
    exit();
}
?>
