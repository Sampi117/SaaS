<?php
header('Content-Type: application/json');
include_once '../config/conexion.php';

try {
    $sql = "SELECT * FROM tb_operaciones ORDER BY nombre";
    $stmt = $pdo->query($sql);
    $operaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($operaciones);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al obtener las operaciones: ' . $e->getMessage()
    ]);
}
