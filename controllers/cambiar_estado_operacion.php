<?php
session_start();
include_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => 'Error al actualizar el estado'];
    
    try {
        $id = (int)($_POST['id'] ?? 0);
        $estado = $_POST['estado'] === 'Activo' ? 'Inactivo' : 'Activo';
        
        $stmt = $pdo->prepare("UPDATE tb_operaciones SET estado = ? WHERE id = ?");
        
        if ($stmt->execute([$estado, $id])) {
            $response = [
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'nuevo_estado' => $estado
            ];
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
