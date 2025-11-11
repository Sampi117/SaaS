<?php
header('Content-Type: application/json');
require_once '../config/conexion.php';

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('ID de pedido no válido');
    }
    
    $id_pedido = (int)$_GET['id'];
    
    // Obtener información del pedido
    $stmt = $pdo->prepare("
        SELECT p.*, c.razon_social as cliente_nombre
        FROM tb_pedidos p
        INNER JOIN tb_clientes c ON p.id_cliente = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$id_pedido]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        throw new Exception('Pedido no encontrado');
    }
    
    // Obtener items del pedido
    $stmt = $pdo->prepare("
        SELECT * FROM tb_pedido_items WHERE id_pedido = ? ORDER BY id
    ");
    $stmt->execute([$id_pedido]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Para cada item, obtener sus detalles
    foreach ($items as &$item) {
        $id_item = $item['id'];
        
        // Obtener tallas
        $stmt = $pdo->prepare("SELECT * FROM tb_pedido_item_tallas WHERE id_pedido_item = ?");
        $stmt->execute([$id_item]);
        $item['tallas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener procesos
        $stmt = $pdo->prepare("
            SELECT pip.*, p.nombre as nombre_proceso
            FROM tb_pedido_item_procesos pip
            INNER JOIN tb_procesos p ON pip.id_proceso = p.id
            WHERE pip.id_pedido_item = ?
        ");
        $stmt->execute([$id_item]);
        $item['procesos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener materiales
        $stmt = $pdo->prepare("SELECT * FROM tb_pedido_item_materiales WHERE id_pedido_item = ?");
        $stmt->execute([$id_item]);
        $item['materiales'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener costos fijos
        $stmt = $pdo->prepare("SELECT * FROM tb_pedido_item_costos_fijos WHERE id_pedido_item = ?");
        $stmt->execute([$id_item]);
        $item['costos_fijos'] = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    $pedido['items'] = $items;
    
    $response['success'] = true;
    $response['data'] = $pedido;
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Error en obtener_pedido.php: ' . $e->getMessage());
}

echo json_encode($response);