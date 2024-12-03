<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    // Si no está autenticado, redirigir al formulario de inicio de sesión
    header("Location: index.php");
    exit();
}

// Obtener el nombre de usuario de la sesión
$nombreUsuario = isset($_SESSION['usuario']) ? $_SESSION['usuario']['nombre'] : '';

include 'Conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="src/style_home.css">
    <title>Partes</title>
    <link rel="icon" href="src/icon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="src/icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.25.0/themes/prism.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

</head>

<body>

<div class="tabs">
    <!-- Asegúrate de que la etiqueta link para el logo esté antes de las pestañas -->
    <div class="logo">
    <img src="src/deltanet-white.png" width="200px">
    </div>

    <div class="tab" onclick="window.location.href='Usuarios.php'">
        <i class="fas fa-users"></i> Usuarios <!-- Icono para Usuarios -->
    </div>
    <div class="tab" onclick="window.location.href='Registros.php'">
    <i class="fas fa-clipboard-list"></i> Registros <!-- Icono para Registros -->
    </div>
    <div class="tab" onclick="window.location.href='Partes.php'">
    <i class="fas fa-tools"></i> Partes<!-- Icono para Partes -->
    </div>
    <div class="tab" onclick="window.location.href='Perfil.php'"><p class="usuario-nombre"><?php echo "<b>Usuario Activo: </b>" . $_SESSION['usuario']['nombre']; ?></p></div>
    <div class="tab" onclick="window.location.href='Perfil.php'"><b>Versión: 1.20</b><br/>DNET-RRHH</div>
    <div class="tab" style="cursor: pointer; color: red;" onclick="window.location.href='logout.php'">Cerrar Sesión</div>
</div>

<div id="Partes" class="tab-content">
    <h2 class="posicionamiento-textos">Partes</h2>
    <p class="posicionamiento-textos">Listado de registros que permite filtrar y descargarlos en formato xlsx.</p>

    <form style="margin-left: 5%; margin-top: 40px;" method="post" action="procesar_partes.php">
    <div style="flex: 0 0 30%;">
        <label for="usuarios">Selecciona un usuario:</label>
        <select id="usuarios" name="usuarios" class="select-partes">
        <?php
            // Realizar la consulta para obtener los nombres completos de los usuarios con su ID
            try {
                $queryUsuarios = "SELECT IdUsuario, CONCAT(COALESCE(Nombre, ''), ' ', COALESCE(Apellidos, '')) AS NombreCompleto FROM TUsuarios";
                $stmt = $conn->prepare($queryUsuarios); // Preparar la consulta
                $stmt->execute(); // Ejecutar la consulta

                echo '<p>Consulta ejecutada correctamente.</p>'; // Depuración

                // Iterar sobre los resultados y mostrar cada fila
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<p>Fila obtenida: ' . print_r($row, true) . '</p>'; // Depuración
                    echo '<option value="' . htmlspecialchars($row['IdUsuario']) . '">' . htmlspecialchars($row['NombreCompleto']) . '</option>';
                }
            } catch (PDOException $e) {
                // Mostrar errores específicos de PDO
                echo '<p>Error en la consulta: ' . $e->getMessage() . '</p>';
            }
            ?>
        </select>
    </div>

    
    <div style="flex: 0 0 30%;">
            <label for="meses">Selecciona un mes:</label>
            <select id="meses" name="meses" class="select-partes">
                <?php
                // Generar opciones para los meses del año
                $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                foreach ($meses as $mes) {
                    echo '<option value="' . $mes . '">' . $mes . '</option>';
                }
                ?>
            </select>
        </div>

        <div style="flex: 0 0 30%;">
            <label for="anios">Selecciona un año:</label>
            <select id="anios" name="anios" class="select-partes">
                <?php
                // Generar opciones para los años
                $anioInicial = 2024; // Año inicial
                $anioFinal = date("Y") + 0; // Año final (5 años en el futuro)
                for ($anio = $anioInicial; $anio <= $anioFinal; $anio++) {
                    $selected = ($anio == date("Y")) ? "selected" : ""; // Verificar si el año es el actual
                    echo '<option value="' . $anio . '" ' . $selected . '>' . $anio . '</option>';
                }
                ?>
            </select>
        </div>


        <button class="btn-partes" type="submit">Enviar</button>
    </form>
</div>


</body>
</html>