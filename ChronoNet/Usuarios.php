<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Obtener el nombre de usuario de la sesión
$nombreUsuario = isset($_SESSION['usuario']) ? $_SESSION['usuario']['nombre'] : '';

include 'Conexion.php';
require_once 'dao/DAOUsuarios.php';

$daoUsuarios = new DAOUsuarios($conn);

// Obtener todos los nombres de usuarios para el combobox
$nombresUsuarios = $daoUsuarios->obtenerTodosLosNombres();

// Filtrados
$filtroUsuario = isset($_GET['filtroUsuario']) ? $_GET['filtroUsuario'] : '';
$filtroNombre = isset($_GET['filtroNombre']) ? $_GET['filtroNombre'] : '';
$filtroAdmin = isset($_GET['filtroAdmin']) ? $_GET['filtroAdmin'] : '';

// Paginación
$usuariosPorPagina = 10;
$paginaUsuarios = isset($_GET['pagina_usuarios']) ? intval($_GET['pagina_usuarios']) : 1;
if ($paginaUsuarios < 1) {
    $paginaUsuarios = 1;
}

// Obtener usuarios filtrados con paginación
$usuarios = $daoUsuarios->obtenerUsuariosConFiltros($filtroUsuario, $filtroNombre, $filtroAdmin, $paginaUsuarios, $usuariosPorPagina);

// Calcular el número total de usuarios para la paginación
$totalUsuarios = $daoUsuarios->contarUsuariosFiltrados($filtroUsuario, $filtroNombre, $filtroAdmin);
$totalPaginasUsuarios = ceil($totalUsuarios / $usuariosPorPagina);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="src/style_home.css">
    <title>Usuarios</title>
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
            <i class="fas fa-users"></i> Usuarios
        </div>
        <div class="tab" onclick="window.location.href='Registros.php'">
            <i class="fas fa-clipboard-list"></i> Registros
        </div>
        <div class="tab" onclick="window.location.href='Partes.php'">
            <i class="fas fa-tools"></i> Partes
        </div>
        <div class="tab" onclick="window.location.href='Perfil.php'">
            <p class="usuario-nombre"><?php echo "<b>Usuario Activo: </b>" . $_SESSION['usuario']['nombre']; ?></p>
        </div>
        <div class="tab" onclick="window.location.href='Perfil.php'"><b>Versión: 1.20</b><br/>DNET-RRHH</div>
        <div class="tab" style="cursor: pointer; color: red;" onclick="window.location.href='logout.php'">Cerrar Sesión</div>
    </div>

    <div id="Usuarios" class="tab-content">
        <h2 class="posicionamiento-textos">Usuarios:</h2>
        <p class="posicionamiento-textos">Desde este apartado puedes visualizar los usuarios en formato tabla y aplicar filtros.</p>

        <!-- Formulario de filtros -->
        <form method="GET" action="Usuarios.php" style="margin-bottom: 20px;">
            <div id="filtros" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
                <div style="flex: 0 0 30%; display: flex; flex-direction: column;">
                    <label for="filtroUsuario">Usuario (DNI):</label>
                    <input type="text" id="filtroUsuario" name="filtroUsuario" value="<?php echo htmlspecialchars($filtroUsuario); ?>" class="input-campo">
                </div>
                <div style="flex: 0 0 30%; display: flex; flex-direction: column;">
                    <label for="filtroNombre">Nombre:</label>
                    <select id="filtroNombre" name="filtroNombre" class="input-campo">
                        <option value="">Todos</option>
                        <?php foreach ($nombresUsuarios as $nombre): ?>
                            <option value="<?php echo htmlspecialchars($nombre['Nombre']); ?>" <?php echo ($filtroNombre === $nombre['Nombre']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($nombre['Nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex: 0 0 20%; display: flex; flex-direction: column;">
                    <label for="filtroAdmin">Administrador:</label>
                    <select id="filtroAdmin" name="filtroAdmin" class="input-campo">
                        <option value="">Todos</option>
                        <option value="1" <?php echo (isset($_GET['filtroAdmin']) && $_GET['filtroAdmin'] == "1") ? 'selected' : ''; ?>>SI</option>
                        <option value="0" <?php echo (isset($_GET['filtroAdmin']) && $_GET['filtroAdmin'] == "0") ? 'selected' : ''; ?>>NO</option>
                    </select>
                </div>
                <div style="flex: 0 0 20%; display: flex; gap: 15px; justify-content: flex-start; align-items: flex-end; margin-top: 10px;">
                    <button type="submit" class="btn-filtrar" style="font-size: 14px; padding: 5px 10px; width: 80px; height: 50px;">Filtrar</button>
                    <button type="reset" class="btn-limpiar" onclick="limpiarFiltros()" style="font-size: 14px; padding: 10px 20px; width: 80px; height: 50px;">Limpiar</button>
                </div>
            </div>
        </form>

        <!-- Tabla de usuarios -->
        <table class="tablas-listado-user" border="1">
            <tr id="header-row">
                <th class="my-cell">ID</th>
                <th class="my-cell">Usuario</th>
                <th class="my-cell">Codigo</th>
                <th class="my-cell">Nombre</th>
                <th class="my-cell">Apellidos</th>
                <th class="my-cell">Administrador</th>
            </tr>
            <?php foreach ($usuarios as $rowUsuarios): ?>
                <tr>
                    <td class="td-list"><?php echo htmlspecialchars($rowUsuarios['IdUsuario']); ?></td>
                    <td class="td-list"><?php echo htmlspecialchars($rowUsuarios['Login']); ?></td>
                    <td class="td-list"><?php echo htmlspecialchars($rowUsuarios['Codigo']); ?></td>
                    <td class="td-list"><?php echo htmlspecialchars($rowUsuarios['Nombre']); ?></td>
                    <td class="td-list"><?php echo htmlspecialchars($rowUsuarios['Apellidos']); ?></td>
                    <td class="td-list"><?php echo ($rowUsuarios['AdminBool'] == "1") ? "SI" : "NO"; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- Paginación -->
        <div class="paginacion">
            <?php for ($i = 1; $i <= $totalPaginasUsuarios; $i++): ?>
                <a href="?pagina_usuarios=<?php echo $i; ?>&filtroUsuario=<?php echo urlencode($filtroUsuario); ?>&filtroNombre=<?php echo urlencode($filtroNombre); ?>&filtroAdmin=<?php echo urlencode($filtroAdmin); ?>" <?php if ($i == $paginaUsuarios) echo 'class="active"'; ?>>
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>

    <script>
        function limpiarFiltros() {
            document.getElementById('filtroUsuario').value = '';
            document.getElementById('filtroNombre').value = '';
            document.getElementById('filtroAdmin').value = '';
            window.location.href = 'Usuarios.php';
        }
    </script>
</body>
</html>
