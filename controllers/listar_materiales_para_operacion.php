<?php
header('Content-Type: application/json');
require_once '../config/conexion.php';

$response = ['success' => false, 'data' => []];

try {
    $id_operacion = isset($_GET['id_operacion']) ? (int)$_GET['id_operacion'] : null;

    if ($id_operacion) {
        // Obtener materiales directos asociados a la operación (vía tb_material_operaciones)
        $sql = "SELECT md.id AS id_material, md.nombre AS nombre_material, md.unidad_medida, md.costo AS costo_unitario
                FROM tb_material_operaciones mo
                JOIN tb_materiales_directos md ON md.id = mo.material_id
                WHERE mo.operacion_id = ? AND md.estado = 'Activo'
                ORDER BY md.nombre ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_operacion]);
        $materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Si no hay mapeos, devolver todos los materiales directos activos como fallback
        if (empty($materiales)) {
            $stmt = $pdo->prepare("SELECT id AS id_material, nombre AS nombre_material, unidad_medida, costo AS costo_unitario FROM tb_materiales_directos WHERE estado = 'Activo' ORDER BY nombre ASC");
            $stmt->execute();
            $materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } else {
        // Si no se pasó id_operacion, devolver todos los directos activos
        $stmt = $pdo->prepare("SELECT id AS id_material, nombre AS nombre_material, unidad_medida, costo AS costo_unitario FROM tb_materiales_directos WHERE estado = 'Activo' ORDER BY nombre ASC");
        $stmt->execute();
        $materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $response['success'] = true;
    $response['data'] = $materiales;

} catch (PDOException $e) {
    $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    error_log('Error PDO en listar_materiales_para_operacion.php: ' . $e->getMessage());
}

echo json_encode($response);
?>
