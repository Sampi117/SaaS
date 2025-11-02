<?php
header('Content-Type: application/json');
require_once '../config/conexion.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método no permitido';
    echo json_encode($response);
    exit;
}

try {
    // Validar datos de entrada
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        throw new Exception('ID de ficha técnica no válido');
    }
    
    if (!isset($_POST['estado']) || !in_array($_POST['estado'], ['0', '1'])) {
        throw new Exception('Estado no válido');
    }
    
    $idFichaTecnica = (int)$_POST['id'];
    $nuevoEstado = (int)$_POST['estado'];
    
    // Verificar que la ficha técnica existe
    $stmt = $pdo->prepare("SELECT id FROM fichas_tecnicas WHERE id = ?");
    $stmt->execute([$idFichaTecnica]);
    
    if (!$stmt->fetch()) {
        throw new Exception('La ficha técnica no existe');
    }
    
    // Actualizar el estado
    $stmt = $pdo->prepare("UPDATE fichas_tecnicas SET estado = ? WHERE id = ?");
    $stmt->execute([$nuevoEstado, $idFichaTecnica]);
    
    $response['success'] = true;
    $response['message'] = 'Estado actualizado correctamente';
    $response['nuevo_estado'] = $nuevoEstado;
    
} catch (Exception $e) {
    $response['message'] = 'Error al actualizar el estado: ' . $e->getMessage();
}

echo json_encode($response);
?>
