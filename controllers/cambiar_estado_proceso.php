<?php
session_start();
header('Content-Type: application/json');
include_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => 'Error al cambiar el estado del proceso'];
    
    try {
        $id = $_POST['id'] ?? '';
        
        if (empty($id)) {
            throw new Exception('ID de proceso no especificado');
        }
        
        // Obtener el estado actual
        $stmt = $pdo->prepare("SELECT estado FROM tb_procesos WHERE id = ?");
        $stmt->execute([$id]);
        $proceso = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$proceso) {
            throw new Exception('Proceso no encontrado');
        }
        
        // Cambiar el estado
        $nuevo_estado = $proceso['estado'] === 'Activo' ? 'Inactivo' : 'Activo';
        
        $stmt = $pdo->prepare("UPDATE tb_procesos SET estado = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $id]);
        
        $response = [
            'success' => true,
            'message' => 'Estado del proceso actualizado correctamente',
            'nuevo_estado' => $nuevo_estado
        ];
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

// Si no es POST, redirigir
header('Location: ../view/procesos.php');
exit;
