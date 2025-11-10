<?php
header('Content-Type: application/json');
require_once '../config/conexion.php';

try {
    $stmt = $pdo->prepare("SELECT id, nombre, descripcion FROM tb_operaciones WHERE estado = 'Activo' ORDER BY nombre");
    $stmt->execute();
    $ops = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $ops]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener operaciones: ' . $e->getMessage()]);
}
