<?php
header('Content-Type: application/json; charset=utf-8');
include '../config/conexion.php';

$response = ['success' => false, 'message' => '', 'data' => []];

try {
    // Obtener el término de búsqueda
    $busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    // Preparar consulta base con JOIN a la tabla de proveedores
    $sql = "SELECT 
                mi.id, 
                mi.nombre, 
                mi.unidad_medida as unidad, 
                ROUND(mi.costo, 2) as costo_unitario,
                mi.cantidad,
                mi.proveedor_id,
                p.nombre as nombre_proveedor
            FROM tb_materiales_indirectos mi
            LEFT JOIN tb_proveedores p ON mi.proveedor_id = p.id";
    
    $params = [];
    
    // Si hay término de búsqueda, agregar condición WHERE
    if (!empty($busqueda)) {
        $sql .= " WHERE (mi.nombre LIKE ? OR CAST(mi.id AS CHAR) LIKE ? OR p.nombre LIKE ?)";
        $searchTerm = "%$busqueda%";
        $params = [$searchTerm, $searchTerm, $searchTerm];
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
        $material['proveedor_id'] = !empty($material['proveedor_id']) ? intval($material['proveedor_id']) : null;
        $material['proveedor'] = $material['nombre_proveedor'] ?? null;
        unset($material['nombre_proveedor']); // Limpiar el campo temporal
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
    error_log('Error PDO en buscar_materiales_indirectos.php: ' . $e->getMessage());
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Error inesperado',
        'error' => $e->getMessage()
    ];
    error_log('Error en buscar_materiales_indirectos.php: ' . $e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
?>