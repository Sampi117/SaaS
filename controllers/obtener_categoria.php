<?php
header('Content-Type: application/json; charset=utf-8');
include '../config/conexion.php';

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('ID de categoría no válido');
    }

    $idCategoria = intval($_GET['id']);

    // Obtener información de la categoría
    $stmt = $pdo->prepare(
        "SELECT id, nombre, costos_indirectos, costos_financieros, 
                costos_distribucion, estado, fecha_creacion
         FROM categorias 
         WHERE id = ?"
    );
    $stmt->execute([$idCategoria]);
    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$categoria) {
        throw new Exception('Categoría no encontrada');
    }

    // Convertir costos a números
    $categoria['costos_indirectos'] = floatval($categoria['costos_indirectos']);
    $categoria['costos_financieros'] = floatval($categoria['costos_financieros']);
    $categoria['costos_distribucion'] = floatval($categoria['costos_distribucion']);

    // Obtener materiales asociados a la categoría
    $stmt = $pdo->prepare(
        "SELECT m.id, m.nombre, m.unidad_medida as unidad, 
                ROUND(m.costo, 2) as costo_unitario, 
                cm.cantidad,
                ROUND((m.costo * cm.cantidad), 2) as costo_total
         FROM categoria_materiales cm
         JOIN tb_materiales_indirectos m ON cm.id_material = m.id
         WHERE cm.id_categoria = ?
         ORDER BY m.nombre"
    );
    $stmt->execute([$idCategoria]);
    $materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convertir valores numéricos
    foreach ($materiales as &$material) {
        $material['costo_unitario'] = floatval($material['costo_unitario']);
        $material['cantidad'] = floatval($material['cantidad']);
        $material['costo_total'] = floatval($material['costo_total']);
    }

    // Calcular total de costos de materiales
    $totalMateriales = array_reduce($materiales, function($carry, $item) {
        return $carry + $item['costo_total'];
    }, 0);

    $response = [
        'success' => true,
        'categoria' => $categoria,
        'materiales' => $materiales,
        'total_materiales' => round($totalMateriales, 2),
        'count_materiales' => count($materiales)
    ];

} catch (PDOException $e) {
    $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    error_log('Error PDO en obtener_categoria.php: ' . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Error en obtener_categoria.php: ' . $e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
?>