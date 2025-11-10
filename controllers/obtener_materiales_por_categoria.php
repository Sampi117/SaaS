<?php
header('Content-Type: application/json');
require_once '../config/conexion.php';

$response = ['success' => false, 'data' => []];

try {
    $id_categoria = isset($_GET['id_categoria']) ? (int)$_GET['id_categoria'] : 0;

    if ($id_categoria <= 0) {
        throw new Exception('ID de categoría inválido');
    }

    // Obtener los materiales asociados a la categoría
    // Nota: la tabla tb_materiales_indirectos no tiene columna 'descripcion' en este esquema,
    // por eso seleccionamos únicamente columnas existentes: id, nombre, unidad_medida, costo, estado
    $sql = "SELECT 
                cm.id as id_rel, 
                cm.id_categoria, 
                cm.id_material as id_material_rel, 
                cm.cantidad as cantidad_default, 
                m.id as id_material, 
                m.nombre as nombre_material, 
                m.unidad_medida, 
                m.costo as costo_unitario, 
                m.estado
            FROM categoria_materiales cm
            INNER JOIN tb_materiales_indirectos m ON cm.id_material = m.id
            WHERE cm.id_categoria = ? AND m.estado = 'Activo'
            ORDER BY m.nombre ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_categoria]);
    $materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener costos por defecto de la categoría
    $stmt2 = $pdo->prepare("SELECT costos_indirectos, costos_financieros, costos_distribucion FROM categorias WHERE id = ? LIMIT 1");
    $stmt2->execute([$id_categoria]);
    $costos = $stmt2->fetch(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = [
        'materiales' => $materiales,
        'costos' => $costos ?: ['costos_indirectos' => 0, 'costos_financieros' => 0, 'costos_distribucion' => 0]
    ];

} catch (PDOException $e) {
    $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    error_log('Error PDO en obtener_materiales_por_categoria.php: ' . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Error en obtener_materiales_por_categoria.php: ' . $e->getMessage());
}

echo json_encode($response);
?>
