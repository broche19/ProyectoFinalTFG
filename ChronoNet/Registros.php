<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

include 'Conexion.php';
require_once 'dao/DAORegistros.php';

// Crear instancia del DAO
$daoRegistros = new DAORegistros($conn);

// Obtener página actual para la paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1; // Asegurarse de que la página no sea menor a 1
}

$recordsPerPage = 10; // Registros por página

// Filtros
$filtroNombreCompleto = isset($_GET['filtroNombreCompleto']) ? $_GET['filtroNombreCompleto'] : '';
$filtroFechaHora = isset($_GET['filtroFechaHora']) ? $_GET['filtroFechaHora'] : '';
$filtroFechaHoraFinal = isset($_GET['filtroFechaHoraFinal']) ? $_GET['filtroFechaHoraFinal'] : '';
$filtroTipo = isset($_GET['filtroTipo']) ? $_GET['filtroTipo'] : '';
$filtroIncidencia = isset($_GET['filtroIncidencia']) ? $_GET['filtroIncidencia'] : '';

// Calcular número total de registros y páginas
$totalRecords = $daoRegistros->contarRegistros($filtroNombreCompleto, $filtroFechaHora, $filtroFechaHoraFinal, $filtroTipo, $filtroIncidencia);
$totalPages = ceil($totalRecords / $recordsPerPage);

// Si la página solicitada es mayor que las páginas totales, ajustarla
if ($page > $totalPages) {
    $page = $totalPages;
}

// Obtener registros con paginación
$registros = $daoRegistros->obtenerRegistrosConPaginacion($filtroNombreCompleto, $filtroFechaHora, $filtroFechaHoraFinal, $filtroTipo, $filtroIncidencia, $page, $recordsPerPage);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="src/style_home.css">
    <link rel="icon" href="src/icon.ico" type="image/x-icon">    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.25.0/themes/prism.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>Registros</title>
</head>
<body>
<div class="tabs">
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

<div id="Registros" class="tab-content">
    <h2 class="posicionamiento-textos">Registros:</h2>
    <p class="posicionamiento-textos">Desde este apartado puedes visualizar los registros en formato tabla y aplicar filtros.</p>

    <form method="GET" action="Registros.php" style="margin-bottom: 30px;">
    <div id="filtros" style="display: flex; flex-direction: column; gap: 15px;">
        <!-- Primera fila -->
        <div style="display: flex; gap: 15px; align-items: flex-start;">
            <div style="flex: 1; display: flex; flex-direction: column;">
                <label for="filtroNombreCompleto">Nombre Completo:</label>
                <input type="text" id="filtroNombreCompleto" name="filtroNombreCompleto" value="<?php echo htmlspecialchars($filtroNombreCompleto); ?>" class="input-campo">
            </div>
            <div style="flex: 1; display: flex; flex-direction: column;">
                <label for="filtroFechaHora">Fecha Inicial:</label>
                <input type="date" id="filtroFechaHora" name="filtroFechaHora" value="<?php echo htmlspecialchars($filtroFechaHora); ?>" class="input-campo">
            </div>
            <div style="flex: 1; display: flex; flex-direction: column;">
                <label for="filtroFechaHoraFinal">Fecha Final:</label>
                <input type="date" id="filtroFechaHoraFinal" name="filtroFechaHoraFinal" value="<?php echo htmlspecialchars($filtroFechaHoraFinal); ?>" class="input-campo">
            </div>
        </div>

        <!-- Segunda fila -->
        <div style="display: flex; gap: 15px; align-items: flex-start;">
            <div style="flex: 1; display: flex; flex-direction: column;">
                <label for="filtroTipo">Tipo de Registro:</label>
                <select id="filtroTipo" name="filtroTipo" class="input-campo">
                    <option value="">Todos</option>
                    <option value="0" <?php echo ($filtroTipo === "0") ? 'selected' : ''; ?>>Entrada</option>
                    <option value="1" <?php echo ($filtroTipo === "1") ? 'selected' : ''; ?>>Salida</option>
                    <option value="2" <?php echo ($filtroTipo === "2") ? 'selected' : ''; ?>>Pausa</option>
                    <option value="3" <?php echo ($filtroTipo === "3") ? 'selected' : ''; ?>>Reanudar</option>
                </select>
            </div>
            <div style="flex: 1; display: flex; flex-direction: column;">
                <label for="filtroIncidencia">Incidencia:</label>
                <select id="filtroIncidencia" name="filtroIncidencia" class="input-campo">
                    <option value="">Todos</option>
                    <option value="SI" <?php echo ($filtroIncidencia === "SI") ? 'selected' : ''; ?>>SI</option>
                    <option value="NO" <?php echo ($filtroIncidencia === "NO") ? 'selected' : ''; ?>>NO</option>
                </select>
            </div>
            <!-- Botones en la esquina inferior izquierda -->
            <div style="display: flex; gap: 40px; align-items: flex-end; margin-top: auto;">
                <button type="submit" class="btn-filtrar">Filtrar</button>
                <button type="reset" class="btn-limpiar" onclick="limpiarFiltros()">Limpiar</button>
            </div>
        </div>
    </div>
</form>


    <div class="contenedor-tabla">
        <table class="tablas-listado-user" border="1">
            <tr id="header-row">
                <th class="my-cell">ID Registro</th>
                <th class="my-cell">Nombre Completo</th>
                <th class="my-cell">Tipo</th>
                <th class="my-cell">Fecha y Hora</th>
                <th class="my-cell">Incidencia</th>
            </tr>
            <?php if (count($registros) > 0): ?>
                <?php foreach ($registros as $registro): ?>
                    <tr>
                        <td class="td-list"><?php echo htmlspecialchars($registro['IdRegistro']); ?></td>
                        <td class="td-list"><?php echo htmlspecialchars($registro['nombrecompleto']); ?></td>
                        <td class="td-list">
                            <?php
                            switch ($registro['Tipo']) {
                                case 0: echo 'Entrada'; break;
                                case 1: echo 'Salida'; break;
                                case 2: echo 'Pausa'; break;
                                case 3: echo 'Reanudar'; break;
                            }
                            ?>
                        </td>
                        <td class="td-list"><?php echo htmlspecialchars($registro['FechaHora']); ?></td>
                        <td class="td-list"><?php echo htmlspecialchars($registro['Incidencia']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="td-list">No se encontraron registros.</td></tr>
            <?php endif; ?>
        </table>

        <div class="paginacion">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&filtroNombreCompleto=<?php echo urlencode($filtroNombreCompleto); ?>&filtroFechaHora=<?php echo urlencode($filtroFechaHora); ?>&filtroFechaHoraFinal=<?php echo urlencode($filtroFechaHoraFinal); ?>&filtroTipo=<?php echo urlencode($filtroTipo); ?>&filtroIncidencia=<?php echo urlencode($filtroIncidencia); ?>" class="pagination-button">Anterior</a>
            <?php endif; ?>

            <a href="?page=1&filtroNombreCompleto=<?php echo urlencode($filtroNombreCompleto); ?>&filtroFechaHora=<?php echo urlencode($filtroFechaHora); ?>&filtroFechaHoraFinal=<?php echo urlencode($filtroFechaHoraFinal); ?>&filtroTipo=<?php echo urlencode($filtroTipo); ?>&filtroIncidencia=<?php echo urlencode($filtroIncidencia); ?>" class="pagination-button <?php echo ($page == 1) ? 'active-page' : ''; ?>">1</a>

            <?php if ($page > 3): ?>
                <span class="pagination-dots">...</span>
            <?php endif; ?>

            <?php if ($page > 2 && $page < $totalPages - 1): ?>
                <a href="?page=<?php echo $page; ?>&filtroNombreCompleto=<?php echo urlencode($filtroNombreCompleto); ?>&filtroFechaHora=<?php echo urlencode($filtroFechaHora); ?>&filtroFechaHoraFinal=<?php echo urlencode($filtroFechaHoraFinal); ?>&filtroTipo=<?php echo urlencode($filtroTipo); ?>&filtroIncidencia=<?php echo urlencode($filtroIncidencia); ?>" class="pagination-button active-page"><?php echo $page; ?></a>
            <?php endif; ?>

            <?php if ($page < $totalPages - 2): ?>
                <span class="pagination-dots">...</span>
            <?php endif; ?>

            <?php if ($totalPages > 1): ?>
                <a href="?page=<?php echo $totalPages; ?>&filtroNombreCompleto=<?php echo urlencode($filtroNombreCompleto); ?>&filtroFechaHora=<?php echo urlencode($filtroFechaHora); ?>&filtroFechaHoraFinal=<?php echo urlencode($filtroFechaHoraFinal); ?>&filtroTipo=<?php echo urlencode($filtroTipo); ?>&filtroIncidencia=<?php echo urlencode($filtroIncidencia); ?>" class="pagination-button <?php echo ($page == $totalPages) ? 'active-page' : ''; ?>"><?php echo $totalPages; ?></a>
            <?php endif; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&filtroNombreCompleto=<?php echo urlencode($filtroNombreCompleto); ?>&filtroFechaHora=<?php echo urlencode($filtroFechaHora); ?>&filtroFechaHoraFinal=<?php echo urlencode($filtroFechaHoraFinal); ?>&filtroTipo=<?php echo urlencode($filtroTipo); ?>&filtroIncidencia=<?php echo urlencode($filtroIncidencia); ?>" class="pagination-button">Siguiente</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function limpiarFiltros() {
        document.getElementById('filtroNombreCompleto').value = '';
        document.getElementById('filtroFechaHora').value = '';
        document.getElementById('filtroFechaHoraFinal').value = '';
        document.getElementById('filtroTipo').value = '';
        document.getElementById('filtroIncidencia').value = '';
        window.location.href = 'Registros.php';
    }
</script>
</body>
</html>
