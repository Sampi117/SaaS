<?php
header('Content-Type: application/json');
require_once '../config/conexion.php';

$response = ['success' => false, 'data' => []];

try {
    $stmt = $pdo->prepare("SELECT id as id_material, nombre as nombre_material, unidad_medida, costo as costo_unitario FROM tb_materiales_directos WHERE estado = 'Activo' ORDER BY nombre ASC");
    $stmt->execute();
    $materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = $materiales;
} catch (PDOException $e) {
    $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    error_log('Error PDO en listar_materiales_directos.php: ' . $e->getMessage());
}

echo json_encode($response);
?>
