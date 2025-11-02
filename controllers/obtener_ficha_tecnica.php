<?php
header('Content-Type: application/json');
require_once '../config/conexion.php';

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('ID de ficha técnica no válido');
    }
    
    $idFichaTecnica = (int)$_GET['id'];
    
    // Obtener información básica de la ficha técnica
    $stmt = $pdo->prepare(
        "SELECT ft.*, c.nombre_categoria 
         FROM fichas_tecnicas ft
         LEFT JOIN categorias c ON ft.id_categoria = c.id_categoria
         WHERE ft.id = ?"
    );
    $stmt->execute([$idFichaTecnica]);
    $ficha = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ficha) {
        throw new Exception('Ficha técnica no encontrada');
    }
    
    // Convertir valores numéricos
    $ficha['id'] = (int)$ficha['id'];
    $ficha['id_categoria'] = (int)$ficha['id_categoria'];
    $ficha['estado'] = (int)$ficha['estado'];
    
    // Obtener tallas
    $stmt = $pdo->prepare("SELECT talla, genero FROM ficha_tecnica_tallas WHERE id_ficha_tecnica = ? ORDER BY talla ASC");
    $stmt->execute([$idFichaTecnica]);
    $ficha['tallas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener procesos
    $stmt = $pdo->prepare(
        "SELECT p.id as id_proceso, p.nombre_proceso, 
                ftp.mano_obra, ftp.liquidacion, ftp.total
         FROM ficha_tecnica_procesos ftp
         JOIN tb_procesos p ON ftp.id_proceso = p.id_proceso
         WHERE ftp.id_ficha_tecnica = ?
         ORDER BY ftp.id ASC"
    );
    $stmt->execute([$idFichaTecnica]);
    $ficha['procesos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular total de procesos
    $ficha['total_procesos'] = 0;
    foreach ($ficha['procesos'] as &$proceso) {
        $proceso['mano_obra'] = (float)$proceso['mano_obra'];
        $proceso['liquidacion'] = (float)$proceso['liquidacion'];
        $proceso['total'] = (float)$proceso['total'];
        $ficha['total_procesos'] += $proceso['total'];
    }
    
    // Obtener materiales
    $stmt = $pdo->prepare(
        "SELECT m.id as id_material, m.nombre_material, 
                m.descripcion, m.unidad_medida,
                ftm.cantidad, ftm.ancho, ftm.alto, 
                ftm.costo_unitario, ftm.total
         FROM ficha_tecnica_materiales ftm
         JOIN tb_materiales_directos m ON ftm.id_material = m.id
         WHERE ftm.id_ficha_tecnica = ?
         ORDER BY ftm.id ASC"
    );
    $stmt->execute([$idFichaTecnica]);
    $ficha['materiales'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular total de materiales
    $ficha['total_materiales'] = 0;
    foreach ($ficha['materiales'] as &$material) {
        $material['cantidad'] = (float)$material['cantidad'];
        $material['ancho'] = (float)$material['ancho'];
        $material['alto'] = (float)$material['alto'];
        $material['costo_unitario'] = (float)$material['costo_unitario'];
        $material['total'] = (float)$material['total'];
        $ficha['total_materiales'] += $material['total'];
    }
    
    // Obtener costos fijos
    $stmt = $pdo->prepare(
        "SELECT costos_indirectos, costos_financieros, costos_distribucion, total_costos_fijos
         FROM ficha_tecnica_costos_fijos 
         WHERE id_ficha_tecnica = ?"
    );
    $stmt->execute([$idFichaTecnica]);
    $costosFijos = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($costosFijos) {
        $ficha['costos_indirectos'] = (float)$costosFijos['costos_indirectos'];
        $ficha['costos_financieros'] = (float)$costosFijos['costos_financieros'];
        $ficha['costos_distribucion'] = (float)$costosFijos['costos_distribucion'];
        $ficha['total_costos_fijos'] = (float)$costosFijos['total_costos_fijos'];
    } else {
        $ficha['costos_indirectos'] = 0;
        $ficha['costos_financieros'] = 0;
        $ficha['costos_distribucion'] = 0;
        $ficha['total_costos_fijos'] = 0;
    }
    
    // Calcular costo total
    $ficha['costo_total'] = $ficha['total_materiales'] + $ficha['total_procesos'] + $ficha['total_costos_fijos'];
    
    $response['success'] = true;
    $response['data'] = $ficha;
    
} catch (PDOException $e) {
    $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    error_log('Error PDO en obtener_ficha_tecnica.php: ' . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Error en obtener_ficha_tecnica.php: ' . $e->getMessage());
}

echo json_encode($response);
?>