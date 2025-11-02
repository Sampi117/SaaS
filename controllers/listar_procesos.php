<?php
// controllers/listar_procesos.php
require_once '../config/conexion.php';

try {
    // Obtener todos los procesos activos con el costo total calculado desde tb_proceso_operaciones
    $sql = "SELECT 
                p.id as id_proceso,
                p.nombre as nombre_proceso,
                p.estado,
                COALESCE(SUM(po.costo), 0) as costo_mano_obra,
                COALESCE(SUM(po.costo_terceros), 0) as costo_terceros_total,
                COALESCE(SUM(po.tiempo_estimado), 0) as tiempo_total
            FROM tb_procesos p
            LEFT JOIN tb_proceso_operaciones po ON p.id = po.proceso_id
            WHERE p.estado = 'Activo'
            GROUP BY p.id, p.nombre, p.estado
            ORDER BY p.nombre ASC";
    
    $stmt = $pdo->query($sql);
    $procesos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear los valores numéricos
    foreach ($procesos as &$proceso) {
        $proceso['costo_total'] = (float)$proceso['costo_mano_obra'];
        $proceso['costo_mano_obra'] = (float)$proceso['costo_mano_obra'];
        $proceso['costo_terceros_total'] = (float)$proceso['costo_terceros_total'];
        $proceso['tiempo_total'] = (float)$proceso['tiempo_total'];
    }
    
} catch (PDOException $e) {
    error_log("Error en listar_procesos.php: " . $e->getMessage());
    $procesos = [];
}
?>