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
        "SELECT ft.*, c.nombre as nombre_categoria 
         FROM fichas_tecnicas ft
         LEFT JOIN categorias c ON ft.id_categoria = c.id
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
        $stmt = $pdo->prepare("
            SELECT talla, genero, cantidad 
            FROM ficha_tecnica_tallas 
            WHERE id_ficha_tecnica = ? 
            ORDER BY talla ASC
        ");
    $stmt->execute([$idFichaTecnica]);
    $ficha['tallas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener procesos con nombres
    $stmt = $pdo->prepare("
        SELECT 
            p.id as id_proceso, 
            p.nombre as nombre_proceso,
            ftp.mano_obra, 
            ftp.liquidacion, 
            ftp.total
        FROM ficha_tecnica_procesos ftp
        JOIN tb_procesos p ON ftp.id_proceso = p.id
        WHERE ftp.id_ficha_tecnica = ?
        ORDER BY ftp.id ASC
    ");
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
    
    // Obtener materiales con nombres
    $stmt = $pdo->prepare("
        SELECT 
            m.id as id_material, 
            m.nombre as nombre_material,
            m.descripcion, 
            m.unidad_medida,
            ftm.cantidad, 
            ftm.ancho, 
            ftm.alto, 
            ftm.costo_unitario, 
            ftm.total
        FROM ficha_tecnica_materiales ftm
        JOIN tb_materiales_directos m ON ftm.id_material = m.id
        WHERE ftm.id_ficha_tecnica = ?
        ORDER BY ftm.id ASC
    ");
    $stmt->execute([$idFichaTecnica]);
    $ficha['materiales'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener materiales indirectos guardados (si la tabla existe) y anexarlos
    try {
        $stmt2 = $pdo->prepare("SELECT mi.id as id_rel, m.id as id_material, m.nombre as nombre_material, m.unidad_medida, mi.cantidad, mi.ancho, mi.alto, mi.costo_unitario, mi.total
            FROM ficha_tecnica_materiales_indirectos mi
            JOIN tb_materiales_indirectos m ON mi.id_material_indirecto = m.id
            WHERE mi.id_ficha_tecnica = ? ORDER BY mi.id ASC");
        $stmt2->execute([$idFichaTecnica]);
        $indirectos = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        if ($indirectos) {
            foreach ($indirectos as $ind) {
                $ficha['materiales'][] = [
                    'id_material' => $ind['id_material'],
                    'nombre_material' => $ind['nombre_material'],
                    'descripcion' => null,
                    'unidad_medida' => $ind['unidad_medida'],
                    'cantidad' => $ind['cantidad'],
                    'ancho' => $ind['ancho'],
                    'alto' => $ind['alto'],
                    'costo_unitario' => $ind['costo_unitario'],
                    'total' => $ind['total']
                ];
            }
        }
    } catch (PDOException $e) {
        // si la tabla no existe o falla, registrar y continuar
        error_log('No se pudo obtener materiales indirectos: ' . $e->getMessage());
    }

    // Obtener operaciones detalladas asociadas a la ficha (si existen)
    $stmt = $pdo->prepare("SELECT fto.*, o.nombre as nombre_operacion FROM ficha_tecnica_operaciones fto LEFT JOIN tb_operaciones o ON fto.id_operacion = o.id WHERE fto.id_ficha_tecnica = ? ORDER BY fto.id ASC");
    $stmt->execute([$idFichaTecnica]);
    $ops = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener materiales asociados a las operaciones de esta ficha (si existen)
    $materialsByOp = [];
    try {
        $stmtMatOps = $pdo->prepare("SELECT mto.*, md.id as id_material, md.nombre as nombre_material, md.unidad_medida, md.costo as costo_unitario
            FROM ficha_tecnica_operacion_materiales mto
            JOIN tb_materiales_directos md ON mto.id_material = md.id
            WHERE mto.id_ficha_operacion IN (SELECT id FROM ficha_tecnica_operaciones WHERE id_ficha_tecnica = ?)
            ORDER BY mto.id ASC");
        $stmtMatOps->execute([$idFichaTecnica]);
        $matOps = $stmtMatOps->fetchAll(PDO::FETCH_ASSOC);
        foreach ($matOps as $mo) {
            $opId = (int)$mo['id_ficha_operacion'];
            if (!isset($materialsByOp[$opId])) $materialsByOp[$opId] = [];
            $materialsByOp[$opId][] = [
                'id' => (int)$mo['id'],
                'id_material' => (int)$mo['id_material'],
                'nombre_material' => $mo['nombre_material'],
                'unidad_medida' => $mo['unidad_medida'],
                'cantidad' => (float)$mo['cantidad'],
                'costo_unitario' => (float)$mo['costo_unitario'],
                'total' => (float)$mo['total']
            ];
        }
    } catch (PDOException $e) {
        // no bloquear si falla, solo registrar
        error_log('No se pudieron obtener materiales por operación: ' . $e->getMessage());
    }

    // Agrupar operaciones por id_proceso dentro de los procesos existentes
    if ($ops) {
        foreach ($ops as $op) {
            // buscar el proceso correspondiente en $ficha['procesos']
            foreach ($ficha['procesos'] as &$proceso) {
                if ((int)$proceso['id_proceso'] === (int)$op['id_proceso']) {
                    if (!isset($proceso['operaciones'])) $proceso['operaciones'] = [];
                    $opMaterials = isset($materialsByOp[(int)$op['id']]) ? $materialsByOp[(int)$op['id']] : [];
                    $proceso['operaciones'][] = [
                        'id' => (int)$op['id'],
                        'id_operacion' => (int)$op['id_operacion'],
                        'nombre_operacion' => $op['nombre_operacion'] ?? $op['nombre'],
                        'descripcion' => $op['descripcion'],
                        'color' => $op['color'],
                        'tiempo_estimado' => (float)$op['tiempo_estimado'],
                        'costo' => isset($op['costo']) ? (float)$op['costo'] : 0,
                        'orden' => (int)$op['orden'],
                        'materiales' => $opMaterials
                    ];
                    break;
                }
            }
            unset($proceso);
        }
    }
    
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
    $stmt = $pdo->prepare("
        SELECT 
            costos_indirectos, 
            costos_financieros, 
            costos_distribucion, 
            total_costos_fijos
        FROM ficha_tecnica_costos_fijos 
        WHERE id_ficha_tecnica = ?
    ");
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

    // Normalizar URL de imagen para que el cliente pueda mostrarla correctamente
    // Si la imagen existe y no es una URL absoluta, prefijar '../' para que desde la vista funcione
    if (!empty($ficha['imagen'])) {
        $img = $ficha['imagen'];
        if (stripos($img, 'http://') === 0 || stripos($img, 'https://') === 0 || strpos($img, '/') === 0) {
            $ficha['imagen_url'] = $img;
        } else {
            $ficha['imagen_url'] = '../' . $img;
        }
    } else {
        $ficha['imagen_url'] = null;
    }
    
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