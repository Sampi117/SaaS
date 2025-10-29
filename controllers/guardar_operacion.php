<?php
session_start();
include_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => 'Error al procesar la solicitud'];
    
    try {
        $nombre = trim($_POST['nombre'] ?? '');
        
        if (empty($nombre)) {
            throw new Exception('El nombre de la operación es requerido');
        }
        
        $estado = 'Activo';
        $stmt = $pdo->prepare("INSERT INTO tb_operaciones (nombre, estado) VALUES (?, ?)");
        
        if ($stmt->execute([$nombre, $estado])) {
            $response = [
                'success' => true,
                'message' => 'Operación agregada correctamente',
                'id' => $pdo->lastInsertId()
            ];
        } else {
            throw new Exception('Error al guardar la operación');
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

header('Location: ../view/procesos.php');
exit;
