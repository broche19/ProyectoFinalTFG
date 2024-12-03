<?php
class DAOUsuarios {
    private $conn;

    // Constructor para establecer la conexión
    public function __construct($connection) {
        $this->conn = $connection;
    }

    // Función para obtener usuarios con paginación
    public function obtenerUsuariosConPaginacion($offset, $usuariosPorPagina) {
        // Consulta adaptada para SQLite (sin CTE ni ROW_NUMBER, usamos LIMIT y OFFSET)
        $queryUsuarios = "
            SELECT *
            FROM TUsuarios
            ORDER BY IdUsuario ASC
            LIMIT :limit OFFSET :offset
        ";

        // Preparar la consulta
        $stmt = $this->conn->prepare($queryUsuarios);
        $stmt->bindValue(':limit', $usuariosPorPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        // Ejecutar y obtener resultados
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $usuarios;
    }

    // Función para obtener el número total de usuarios
    public function obtenerTotalUsuarios() {
        $queryTotalUsuarios = "SELECT COUNT(*) AS totalUsuarios FROM TUsuarios";
        $stmt = $this->conn->prepare($queryTotalUsuarios);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['totalUsuarios'];
    }

    public function obtenerTodosLosNombres() {
        $query = "SELECT DISTINCT Nombre FROM TUsuarios ORDER BY Nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerUsuariosConFiltros($usuario, $nombre, $admin, $pagina, $porPagina) {
        $offset = ($pagina - 1) * $porPagina;
        $query = "SELECT * FROM TUsuarios WHERE 1=1";
    
        // Aplicar filtros dinámicamente
        if (!empty($usuario)) {
            $query .= " AND Login LIKE :usuario";
        }
        if (!empty($nombre)) {
            $query .= " AND Nombre LIKE :nombre";
        }
        if ($admin !== '') { // Si se seleccionó un filtro de administrador
            $query .= " AND AdminBool = :admin";
        }
    
        $query .= " ORDER BY IdUsuario ASC LIMIT :offset, :limit";
    
        $stmt = $this->conn->prepare($query);
    
        // Asignar valores a los parámetros
        if (!empty($usuario)) {
            $stmt->bindValue(':usuario', "%$usuario%", PDO::PARAM_STR);
        }
        if (!empty($nombre)) {
            $stmt->bindValue(':nombre', "%$nombre%", PDO::PARAM_STR);
        }
        if ($admin !== '') {
            $stmt->bindValue(':admin', $admin, PDO::PARAM_INT);
        }
    
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
        $stmt->execute();
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function contarUsuariosFiltrados($usuario, $nombre, $admin) {
        $query = "SELECT COUNT(*) as total FROM TUsuarios WHERE 1=1";
    
        // Aplicar filtros dinámicamente
        if (!empty($usuario)) {
            $query .= " AND Login LIKE :usuario";
        }
        if (!empty($nombre)) {
            $query .= " AND Nombre LIKE :nombre";
        }
        if ($admin !== '') { // Si se seleccionó un filtro de administrador
            $query .= " AND AdminBool = :admin";
        }
    
        $stmt = $this->conn->prepare($query);
    
        // Asignar valores a los parámetros
        if (!empty($usuario)) {
            $stmt->bindValue(':usuario', "%$usuario%", PDO::PARAM_STR);
        }
        if (!empty($nombre)) {
            $stmt->bindValue(':nombre', "%$nombre%", PDO::PARAM_STR);
        }
        if ($admin !== '') {
            $stmt->bindValue(':admin', $admin, PDO::PARAM_INT);
        }
    
        $stmt->execute();
    
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
}
?>
