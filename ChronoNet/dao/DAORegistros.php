<?php
class DAORegistros {
    private $conn;

    public function __construct($connection) {
        $this->conn = $connection;
    }

    public function contarRegistros($filtroNombreCompleto, $filtroFechaHora, $filtroFechaHoraFinal, $filtroTipo, $filtroIncidencia) {
        $query = "SELECT COUNT(*) FROM TRegistro r 
                  JOIN TUsuarios u ON r.IdUsuario = u.IdUsuario 
                  WHERE 1=1";
        $params = [];
    
        if (!empty($filtroNombreCompleto)) {
            $query .= " AND CONCAT(u.Nombre, ' ', u.Apellidos) LIKE :filtroNombreCompleto";
            $params[':filtroNombreCompleto'] = "%$filtroNombreCompleto%";
        }
        if (!empty($filtroFechaHora)) {
            $query .= " AND DATE(r.FechaHora) >= :filtroFechaHora";
            $params[':filtroFechaHora'] = $filtroFechaHora;
        }
        if (!empty($filtroFechaHoraFinal)) {
            $query .= " AND DATE(r.FechaHora) <= :filtroFechaHoraFinal";
            $params[':filtroFechaHoraFinal'] = $filtroFechaHoraFinal;
        }
        if ($filtroTipo !== '') {
            $query .= " AND r.Tipo = :filtroTipo";
            $params[':filtroTipo'] = $filtroTipo;
        }
        if ($filtroIncidencia !== '') {
            $query .= " AND r.Incidencia = :filtroIncidencia";
            $params[':filtroIncidencia'] = $filtroIncidencia;
        }
    
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    

    public function obtenerRegistrosConPaginacion($filtroNombreCompleto, $filtroFechaHora, $filtroFechaHoraFinal, $filtroTipo, $filtroIncidencia, $page, $recordsPerPage) {
        $offset = ($page - 1) * $recordsPerPage;
    
        $query = "SELECT r.IdRegistro, CONCAT(u.Nombre, ' ', u.Apellidos) AS nombrecompleto, r.Tipo, r.FechaHora, r.Incidencia
                  FROM TRegistro r
                  JOIN TUsuarios u ON r.IdUsuario = u.IdUsuario
                  WHERE 1=1";
        $params = [];
    
        if (!empty($filtroNombreCompleto)) {
            $query .= " AND CONCAT(u.Nombre, ' ', u.Apellidos) LIKE :filtroNombreCompleto";
            $params[':filtroNombreCompleto'] = "%$filtroNombreCompleto%";
        }
        if (!empty($filtroFechaHora)) {
            $query .= " AND DATE(r.FechaHora) >= :filtroFechaHora";
            $params[':filtroFechaHora'] = $filtroFechaHora;
        }
        if (!empty($filtroFechaHoraFinal)) {
            $query .= " AND DATE(r.FechaHora) <= :filtroFechaHoraFinal";
            $params[':filtroFechaHoraFinal'] = $filtroFechaHoraFinal;
        }
        if ($filtroTipo !== '') {
            $query .= " AND r.Tipo = :filtroTipo";
            $params[':filtroTipo'] = $filtroTipo;
        }
        if ($filtroIncidencia !== '') {
            $query .= " AND r.Incidencia = :filtroIncidencia";
            $params[':filtroIncidencia'] = $filtroIncidencia;
        }
    
        $query .= " ORDER BY r.FechaHora DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
    
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
?>
