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
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Datos no válidos');
    }
    
    // Validar campos requeridos
    if (empty($data['id_cliente']) || empty($data['numero_pedido']) || 
        empty($data['fecha_orden']) || empty($data['fecha_entrega']) || 
        empty($data['articulos'])) {
        throw new Exception('Faltan datos requeridos');
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Insertar pedido principal
    $stmt = $pdo->prepare("
        INSERT INTO tb_pedidos (numero_pedido, id_cliente, fecha_orden, fecha_entrega, estado, observaciones)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $data['numero_pedido'],
        $data['id_cliente'],
        $data['fecha_orden'],
        $data['fecha_entrega'],
        $data['estado'] ?? 'Guardado',
        $data['observaciones'] ?? null
    ]);
    
    $id_pedido = $pdo->lastInsertId();
    
    // Insertar cada artículo del pedido
    foreach ($data['articulos'] as $articulo) {
        // Insertar item del pedido
        $stmt = $pdo->prepare("
            INSERT INTO tb_pedido_items 
            (id_pedido, id_ficha_tecnica_original, referencia_personalizada, color, suela, horma, descripcion)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $id_pedido,
            $articulo['id_ficha_original'] ?? null,
            $articulo['referencia'],
            $articulo['color'],
            $articulo['suela'],
            $articulo['horma'] ?? null,
            $articulo['descripcion'] ?? null
        ]);
        
        $id_item = $pdo->lastInsertId();
        
        // Insertar tallas
        if (!empty($articulo['tallas'])) {
            $stmt = $pdo->prepare("
                INSERT INTO tb_pedido_item_tallas (id_pedido_item, talla, genero, cantidad)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($articulo['tallas'] as $talla) {
                $stmt->execute([
                    $id_item,
                    $talla['talla'],
                    $talla['genero'],
                    $talla['cantidad'] ?? 0
                ]);
            }
        }
        
        // Insertar procesos
        if (!empty($articulo['procesos'])) {
            $stmt = $pdo->prepare("
                INSERT INTO tb_pedido_item_procesos (id_pedido_item, id_proceso, mano_obra, liquidacion, total)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($articulo['procesos'] as $proceso) {
                $stmt->execute([
                    $id_item,
                    $proceso['id_proceso'],
                    $proceso['mano_obra'] ?? 0,
                    $proceso['liquidacion'] ?? 0,
                    $proceso['total'] ?? 0
                ]);
            }
        }
        
        // Insertar materiales
        if (!empty($articulo['materiales'])) {
            $stmt = $pdo->prepare("
                INSERT INTO tb_pedido_item_materiales 
                (id_pedido_item, id_material, tipo_material, cantidad, ancho, alto, costo_unitario, total)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($articulo['materiales'] as $material) {
                $stmt->execute([
                    $id_item,
                    $material['id_material'],
                    $material['tipo_material'] ?? 'indirecto',
                    $material['cantidad'] ?? 0,
                    $material['ancho'] ?? 0,
                    $material['alto'] ?? 0,
                    $material['costo_unitario'] ?? 0,
                    $material['total'] ?? 0
                ]);
            }
        }
        
        // Insertar costos fijos
        $stmt = $pdo->prepare("
            INSERT INTO tb_pedido_item_costos_fijos 
            (id_pedido_item, costos_indirectos, costos_financieros, costos_distribucion, total_costos_fijos)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $costos_indirectos = $articulo['costos_indirectos'] ?? 0;
        $costos_financieros = $articulo['costos_financieros'] ?? 0;
        $costos_distribucion = $articulo['costos_distribucion'] ?? 0;
        $total_fijos = $costos_indirectos + $costos_financieros + $costos_distribucion;
        
        $stmt->execute([
            $id_item,
            $costos_indirectos,
            $costos_financieros,
            $costos_distribucion,
            $total_fijos
        ]);
    }
    
    // Confirmar transacción
    $pdo->commit();
    
    $response['success'] = true;
    $response['message'] = 'Pedido guardado correctamente';
    $response['id'] = $id_pedido;
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    error_log('Error PDO en guardar_pedido.php: ' . $e->getMessage());
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response['message'] = $e->getMessage();
    error_log('Error en guardar_pedido.php: ' . $e->getMessage());
}

echo json_encode($response);