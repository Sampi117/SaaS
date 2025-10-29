<?php
require_once '../config/conexion.php';

header('Content-Type: application/json');

try {
    if (empty($_GET['id'])) {
        throw new Exception('ID de proceso no especificado');
    }

    // Obtener información del proceso
    $stmt = $pdo->prepare("SELECT * FROM tb_procesos WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $proceso = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$proceso) {
        throw new Exception('Proceso no encontrado');
    }

    // Obtener detalles de las operaciones con sus costos y tiempos individuales
    $stmt = $pdo->prepare("
        SELECT 
            o.id as operacion_id, 
            o.nombre, 
            po.costo, 
            po.costo_terceros, 
            po.tiempo_estimado, 
            po.orden
        FROM tb_proceso_operaciones po
        INNER JOIN tb_operaciones o ON po.operacion_id = o.id
        WHERE po.proceso_id = ?
        ORDER BY po.orden ASC
    ");
    $stmt->execute([$_GET['id']]);
    $operaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Asegurarse de que los valores numéricos sean números
    foreach ($operaciones as &$op) {
        $op['operacion_id'] = (int)$op['operacion_id'];
        $op['costo'] = (float)$op['costo'];
        $op['costo_terceros'] = (float)$op['costo_terceros'];
        $op['tiempo_estimado'] = (float)$op['tiempo_estimado'];
        $op['orden'] = (int)$op['orden'];
    }

    $proceso['operaciones'] = $operaciones;
    
    // Asegurarse de que los totales también sean números
    $proceso['id'] = (int)$proceso['id'];
    $proceso['costo'] = (float)$proceso['costo'];
    $proceso['costo_terceros'] = (float)$proceso['costo_terceros'];
    $proceso['tiempo_max_entrega'] = (float)$proceso['tiempo_max_entrega'];

    echo json_encode($proceso);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}