<?php
header('Content-Type: application/json; charset=utf-8');
include '../config/conexion.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar datos de entrada
        $id = intval($_POST['id'] ?? 0);
        $estado = trim($_POST['estado'] ?? '');

        if ($id <= 0) {
            throw new Exception('ID de categoría no válido');
        }

        if (!in_array($estado, ['Activo', 'Inactivo'])) {
            throw new Exception('Estado no válido');
        }

        // Verificar que la categoría existe
        $stmt = $pdo->prepare("SELECT id FROM categorias WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            throw new Exception('Categoría no encontrada');
        }

        // Actualizar el estado
        $stmt = $pdo->prepare(
            "UPDATE categorias 
             SET estado = ? 
             WHERE id = ?"
        );
        
        $stmt->execute([$estado, $id]);

        $response = [
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'nuevo_estado' => $estado
        ];

    } catch (PDOException $e) {
        $response['message'] = 'Error en la base de datos';
        error_log('Error PDO en actualizar_estado_categoria.php: ' . $e->getMessage());
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
        error_log('Error en actualizar_estado_categoria.php: ' . $e->getMessage());
    }
} else {
    $response['message'] = 'Método no permitido';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>