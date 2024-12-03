<?php
try {
    // Datos de conexión
    $host = '127.0.0.1'; // Dirección del servidor XAMPP (localhost o IP local)
    $dbname = 'crononet'; // Nombre de la base de datos
    $username = 'root'; // Usuario (por defecto en XAMPP es 'root')
    $password = 'root'; // Contraseña (por defecto en XAMPP es vacía, si configuraste una cámbiala)

    // Crear la conexión PDO a MySQL
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Configurar el manejo de errores de PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Confirmar que la conexión fue exitosa (opcional para pruebas)
    // echo "Conexión exitosa";

} catch (PDOException $e) {
    // Manejar errores de conexión
    die("Error en la conexión: " . $e->getMessage());
}
?>
