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
    
        $checkStmt = $pdo->query("SHOW COLUMNS FROM fichas_tecnicas");
        $columnsInfo = $checkStmt->fetchAll(PDO::FETCH_COLUMN, 0);

        // Determinar el nombre correcto de la columna de categoría (compatibilidad)
        $columnName = in_array('id_categoria', $columnsInfo) ? 'id_categoria' : (in_array('categoria', $columnsInfo) ? 'categoria' : 'id_categoria');

        // Construir dinámicamente la lista de columnas a insertar para mantener compatibilidad
        $cols = ['referencia', $columnName, 'color', 'suela', 'descripcion', 'imagen', 'fecha_creacion', 'estado'];
        $placeholders = array_fill(0, count($cols), '?');
        $values = [
            $_POST['referencia'],
            (int)$_POST['id_categoria'],
            $_POST['color'],
            $_POST['suela'],
            $_POST['descripcion'] ?? null,
            $imagen_path,
            $_POST['fecha_creacion'],
            1
        ];

        // Si existe la columna 'horma', insertarla también
        if (in_array('horma', $columnsInfo)) {
            // Insert horma before descripcion for readability (optional)
            // we'll append it to columns and values
            $cols = array_merge(array_slice($cols, 0, 4), ['horma'], array_slice($cols, 4));
            array_splice($placeholders, 4, 0, array('?'));
            array_splice($values, 4, 0, [$_POST['horma'] ?? null]);
        }

        $sql = "INSERT INTO fichas_tecnicas (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
    
    $id_ficha_tecnica = $pdo->lastInsertId();
    
    // 2. INSERTAR TALLAS (si vienen)
    if (!empty($_POST['tallas'])) {
        // Detectar si la columna 'cantidad' existe en la tabla ficha_tecnica_tallas
        $colStmt = $pdo->query("SHOW COLUMNS FROM ficha_tecnica_tallas LIKE 'cantidad'");
        $hasCantidad = (bool)$colStmt->fetch(PDO::FETCH_ASSOC);

        if ($hasCantidad) {
            $stmt = $pdo->prepare(
                "INSERT INTO ficha_tecnica_tallas (id_ficha_tecnica, talla, genero, cantidad) VALUES (?, ?, ?, ?)"
            );

            foreach ($_POST['tallas'] as $talla) {
                if (isset($talla['talla']) && isset($talla['genero'])) {
                    $cantidad = (float)($talla['cantidad'] ?? 0);
                    $stmt->execute([
                        $id_ficha_tecnica,
                        (int)$talla['talla'],
                        $talla['genero'],
                        $cantidad
                    ]);
                }
            }
        } else {
            // Compatibilidad: insertar sin la columna cantidad
            $stmt = $pdo->prepare(
                "INSERT INTO ficha_tecnica_tallas (id_ficha_tecnica, talla, genero) VALUES (?, ?, ?)"
            );

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

    // Insertar operaciones por proceso (tabla ficha_tecnica_operaciones) y sus materiales (ficha_tecnica_operacion_materiales)
    try {
    $stmtOp = $pdo->prepare("INSERT INTO ficha_tecnica_operaciones (id_ficha_tecnica, id_proceso, id_operacion, descripcion, color, tiempo_estimado, costo, orden) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtOpMat = $pdo->prepare("INSERT INTO ficha_tecnica_operacion_materiales (id_ficha_operacion, id_material, cantidad, costo_unitario, total) VALUES (?, ?, ?, ?, ?)");

        foreach ($_POST['procesos'] as $pIndex => $proceso) {
            // solo si vienen operaciones para este proceso
            if (!empty($proceso['operaciones']) && is_array($proceso['operaciones'])) {
                foreach ($proceso['operaciones'] as $oIndex => $operacion) {
                    if (empty($operacion['id_operacion'])) continue;

                    $descripcion = $operacion['descripcion'] ?? null;
                    $color = $operacion['color'] ?? null;
                    $tiempo = (float)($operacion['tiempo_estimado'] ?? 0);
                    $costoOp = (float)($operacion['costo'] ?? 0);
                    $orden = ($oIndex + 1);

                    // insertar en ficha_tecnica_operaciones
                    $stmtOp->execute([
                        $id_ficha_tecnica,
                        (int)($proceso['id_proceso'] ?? null),
                        (int)$operacion['id_operacion'],
                        $descripcion,
                        $color,
                        $tiempo,
                        $costoOp,
                        $orden
                    ]);

                    $id_ficha_operacion = $pdo->lastInsertId();

                    // insertar materiales asociados a esta operación si vienen
                    if (!empty($operacion['materiales']) && is_array($operacion['materiales'])) {
                        foreach ($operacion['materiales'] as $mat) {
                            if (empty($mat['id_material'])) continue;
                            $cantidadMat = (float)($mat['cantidad'] ?? 0);
                            $costoUnit = (float)($mat['costo_unitario'] ?? 0);
                            $totalMat = (float)($mat['total'] ?? 0);

                            $stmtOpMat->execute([
                                $id_ficha_operacion,
                                (int)$mat['id_material'],
                                $cantidadMat,
                                $costoUnit,
                                $totalMat
                            ]);
                        }
                    }
                }
            }
        }
    } catch (PDOException $e) {
        // Si falla la inserción de operaciones/materiales, lanzar excepción para hacer rollback
        throw $e;
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
        
            // Prepared statements for direct and indirect target tables
            $stmtDirect = $pdo->prepare("INSERT INTO ficha_tecnica_materiales (id_ficha_tecnica, id_material, cantidad, ancho, alto, costo_unitario, total) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtIndirect = $pdo->prepare("INSERT INTO ficha_tecnica_materiales_indirectos (id_ficha_tecnica, id_material_indirecto, cantidad, ancho, alto, costo_unitario, total) VALUES (?, ?, ?, ?, ?, ?, ?)");

            // Helpers to detect whether id belongs to direct or indirect table
            $chkDirect = $pdo->prepare("SELECT id FROM tb_materiales_directos WHERE id = ? LIMIT 1");
            $chkIndirect = $pdo->prepare("SELECT id FROM tb_materiales_indirectos WHERE id = ? LIMIT 1");

            foreach ($_POST['materiales'] as $material) {
                if (empty($material['id_material'])) continue;

                $idMaterial = (int)$material['id_material'];
                $cantidad = (float)($material['cantidad'] ?? 1);
                $ancho = (float)($material['ancho'] ?? 0);
                $alto = (float)($material['alto'] ?? 0);
                $costo_unitario = (float)($material['costo_unitario'] ?? 0);
                $total = (float)($material['total'] ?? 0);

                // comprobar si existe en directos
                $chkDirect->execute([$idMaterial]);
                $isDirect = (bool)$chkDirect->fetch(PDO::FETCH_ASSOC);

                if ($isDirect) {
                    $stmtDirect->execute([
                        $id_ficha_tecnica,
                        $idMaterial,
                        $cantidad,
                        $ancho,
                        $alto,
                        $costo_unitario,
                        $total
                    ]);
                } else {
                    // si no es directo, intentar insertarlo como indirecto
                    $chkIndirect->execute([$idMaterial]);
                    $isIndirect = (bool)$chkIndirect->fetch(PDO::FETCH_ASSOC);
                    if ($isIndirect) {
                        $stmtIndirect->execute([
                            $id_ficha_tecnica,
                            $idMaterial,
                            $cantidad,
                            $ancho,
                            $alto,
                            $costo_unitario,
                            $total
                        ]);
                    } else {
                        // material desconocido: omitir o lanzar excepción (aquí omitimos y registramos)
                        error_log("material id={$idMaterial} no encontrado en directos ni indirectos, se omite");
                    }
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