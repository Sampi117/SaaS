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
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Validar datos básicos
    if (empty($_POST['referencia']) || empty($_POST['id_categoria']) || empty($_POST['color']) || 
        empty($_POST['suela']) || empty($_POST['fecha_creacion'])) {
        throw new Exception('Faltan datos básicos requeridos');
    }
    
    // Procesar imagen si existe
    $imagen_path = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/fichas/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Formato de imagen no permitido');
        }
        
        if ($_FILES['imagen']['size'] > 2097152) { // 2MB
            throw new Exception('La imagen no debe superar 2MB');
        }
        
        $fileName = uniqid('ficha_') . '.' . $fileExtension;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $targetPath)) {
            $imagen_path = 'uploads/fichas/' . $fileName;
        }
    }
    
    // 1. INSERTAR FICHA TÉCNICA
    // Verificar primero qué columnas tiene la tabla
    $checkStmt = $pdo->query("SHOW COLUMNS FROM fichas_tecnicas LIKE '%categoria%'");
    $categoriaColumn = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    // Determinar el nombre correcto de la columna
    $columnName = 'id_categoria'; // Por defecto
    if ($categoriaColumn && isset($categoriaColumn['Field'])) {
        $columnName = $categoriaColumn['Field'];
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO fichas_tecnicas (
            referencia, 
            {$columnName}, 
            color, 
            suela, 
            descripcion, 
            imagen, 
            fecha_creacion, 
            estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 1)
    ");
    
    $stmt->execute([
        $_POST['referencia'],
        (int)$_POST['id_categoria'],
        $_POST['color'],
        $_POST['suela'],
        $_POST['descripcion'] ?? null,
        $imagen_path,
        $_POST['fecha_creacion']
    ]);
    
    $id_ficha_tecnica = $pdo->lastInsertId();
    
    // 2. INSERTAR TALLAS
    if (!empty($_POST['tallas'])) {
        $stmt = $pdo->prepare("
            INSERT INTO ficha_tecnica_tallas (id_ficha_tecnica, talla, genero) 
            VALUES (?, ?, ?)
        ");
        
        foreach ($_POST['tallas'] as $talla) {
            if (isset($talla['talla']) && isset($talla['genero'])) {
                $stmt->execute([
                    $id_ficha_tecnica,
                    (int)$talla['talla'],
                    $talla['genero']
                ]);
            }
        }
    }
    
    // 3. INSERTAR PROCESOS
    if (!empty($_POST['procesos'])) {
        $stmt = $pdo->prepare("
            INSERT INTO ficha_tecnica_procesos (
                id_ficha_tecnica, 
                id_proceso, 
                mano_obra, 
                liquidacion, 
                total
            ) VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($_POST['procesos'] as $proceso) {
            if (!empty($proceso['id_proceso'])) {
                $mano_obra = (float)($proceso['mano_obra'] ?? 0);
                $liquidacion = (float)($proceso['liquidacion'] ?? 0);
                $total = (float)($proceso['total'] ?? 0);
                
                $stmt->execute([
                    $id_ficha_tecnica,
                    (int)$proceso['id_proceso'],
                    $mano_obra,
                    $liquidacion,
                    $total
                ]);
            }
        }
    }
    
    // 4. INSERTAR MATERIALES
    if (!empty($_POST['materiales'])) {
        $stmt = $pdo->prepare("
            INSERT INTO ficha_tecnica_materiales (
                id_ficha_tecnica, 
                id_material, 
                cantidad, 
                ancho, 
                alto, 
                costo_unitario, 
                total
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($_POST['materiales'] as $material) {
            if (!empty($material['id_material'])) {
                $cantidad = (float)($material['cantidad'] ?? 1);
                $ancho = (float)($material['ancho'] ?? 0);
                $alto = (float)($material['alto'] ?? 0);
                $costo_unitario = (float)($material['costo_unitario'] ?? 0);
                $total = (float)($material['total'] ?? 0);
                
                $stmt->execute([
                    $id_ficha_tecnica,
                    (int)$material['id_material'],
                    $cantidad,
                    $ancho,
                    $alto,
                    $costo_unitario,
                    $total
                ]);
            }
        }
    }
    
    // 5. INSERTAR COSTOS FIJOS
    $costos_indirectos = (float)($_POST['costos_indirectos'] ?? 0);
    $costos_financieros = (float)($_POST['costos_financieros'] ?? 0);
    $costos_distribucion = (float)($_POST['costos_distribucion'] ?? 0);
    
    $stmt = $pdo->prepare("
        INSERT INTO ficha_tecnica_costos_fijos (
            id_ficha_tecnica, 
            costos_indirectos, 
            costos_financieros, 
            costos_distribucion
        ) VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $id_ficha_tecnica,
        $costos_indirectos,
        $costos_financieros,
        $costos_distribucion
    ]);
    
    // Confirmar transacción
    $pdo->commit();
    
    $response['success'] = true;
    $response['message'] = 'Ficha técnica guardada correctamente';
    $response['id'] = $id_ficha_tecnica;
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $error_msg = $e->getMessage();
    
    // Mensajes de error más amigables
    if (strpos($error_msg, 'Duplicate entry') !== false) {
        $response['message'] = 'Ya existe una ficha técnica con esa referencia';
    } elseif (strpos($error_msg, 'id_categoria') !== false) {
        $response['message'] = 'Error: Columna de categoría no encontrada. Contacte al administrador.';
    } else {
        $response['message'] = 'Error en la base de datos: ' . $error_msg;
    }
    
    error_log('Error PDO en guardar_ficha_tecnica.php: ' . $error_msg);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response['message'] = $e->getMessage();
    error_log('Error en guardar_ficha_tecnica.php: ' . $e->getMessage());
}

echo json_encode($response);
?>