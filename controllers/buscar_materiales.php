<?php
header('Content-Type: application/json; charset=utf-8');
include '../config/conexion.php';

$response = ['success' => false, 'message' => '', 'data' => []];

try {
    // Obtener el término de búsqueda
    $busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    // Preparar consulta base
    $sql = "SELECT 
                id, 
                nombre, 
                unidad_medida as unidad, 
                ROUND(costo, 2) as costo_unitario,
                cantidad
            FROM tb_materiales_indirectos";
    
    $params = [];
    
    // Si hay término de búsqueda, agregar condición WHERE
    if (!empty($busqueda)) {
        $sql .= " WHERE (nombre LIKE ? OR CAST(id AS CHAR) LIKE ?)";
        $searchTerm = "%$busqueda%";
        $params = [$searchTerm, $searchTerm];
    }
    
    $sql .= " ORDER BY nombre ASC LIMIT 100";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convertir valores numéricos
    foreach ($materiales as &$material) {
        $material['id'] = intval($material['id']);
        $material['costo_unitario'] = floatval($material['costo_unitario']);
        $material['cantidad'] = floatval($material['cantidad'] ?? 0);
    }
    
    $response = [
        'success' => true,
        'data' => $materiales,
        'count' => count($materiales),
        'query' => $busqueda
    ];
    
} catch (PDOException $e) {
    $response = [
        'success' => false,
        'message' => 'Error en la búsqueda de materiales',
        'error' => $e->getMessage()
    ];
    error_log('Error PDO en buscar_materiales.php: ' . $e->getMessage());
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Error inesperado',
        'error' => $e->getMessage()
    ];
    error_log('Error en buscar_materiales.php: ' . $e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
?>