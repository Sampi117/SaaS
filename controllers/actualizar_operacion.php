<?php
session_start();
header('Content-Type: application/json');
include_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => 'Error al actualizar la operación'];
    
    try {
        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        
        if (empty($nombre)) {
            throw new Exception('El nombre de la operación es requerido');
        }
        
        // Verificar si ya existe una operación con el mismo nombre
        $sql_check = "SELECT id FROM tb_operaciones WHERE nombre = ? AND id != ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$nombre, $id]);
        
        if ($stmt_check->rowCount() > 0) {
            throw new Exception('Ya existe una operación con ese nombre');
        }
        
        // Actualizar la operación
        $sql = "UPDATE tb_operaciones SET nombre = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$nombre, $id])) {
            $response = [
                'success' => true,
                'message' => 'Operación actualizada correctamente'
            ];
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

header('Location: ../view/procesos.php');
exit;
