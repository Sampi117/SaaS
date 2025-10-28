<?php
include("../config/conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Iniciar transacción
        $pdo->beginTransaction();

        // Procesar la imagen si se subió
        $imagen_url = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/materiales/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExtension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('mat_') . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $targetPath)) {
                $imagen_url = 'uploads/materiales/' . $fileName;
            }
        }

        // Procesar las tallas
        $tallas = isset($_POST['tallas']) ? 
                 array_map('trim', explode(',', $_POST['tallas'])) : [];
        $tallas_json = json_encode($tallas);

        // Insertar el material directo
        $sql = "INSERT INTO tb_materiales_directos (
            nombre, descripcion, unidad_medida, referencia, horma, 
            genero, tallas_disponibles, calibre, ancho, alto, 
            peso, imagen_url, proveedor_id, costo, estado
        ) VALUES (
            :nombre, :descripcion, :unidad_medida, :referencia, :horma, 
            :genero, :tallas_disponibles, :calibre, :ancho, :alto, 
            :peso, :imagen_url, :proveedor_id, :costo, 'Activo'
        )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $_POST['nombre'],
            ':descripcion' => $_POST['descripcion'] ?? null,
            ':unidad_medida' => $_POST['unidad_medida'],
            ':referencia' => $_POST['referencia'] ?? null,
            ':horma' => $_POST['horma'] ?? null,
            ':genero' => $_POST['genero'],
            ':tallas_disponibles' => $tallas_json,
            ':calibre' => $_POST['calibre'] ?? null,
            ':ancho' => !empty($_POST['ancho']) ? (float)$_POST['ancho'] : null,
            ':alto' => !empty($_POST['alto']) ? (float)$_POST['alto'] : null,
            ':peso' => !empty($_POST['peso']) ? (float)$_POST['peso'] : null,
            ':imagen_url' => $imagen_url,
            ':proveedor_id' => (int)$_POST['proveedor_id'],
            ':costo' => (float)$_POST['costo']
        ]);
        
        $material_id = $pdo->lastInsertId();
        
        // Procesar operaciones existentes
        if (!empty($_POST['operaciones'])) {
            $sql = "INSERT INTO tb_material_operaciones (material_id, operacion_id) 
                    VALUES (:material_id, :operacion_id)";
            $stmt = $pdo->prepare($sql);
            
            foreach ($_POST['operaciones'] as $operacion_id) {
                $stmt->execute([
                    ':material_id' => $material_id,
                    ':operacion_id' => (int)$operacion_id
                ]);
            }
        }
        
        // Procesar nuevas operaciones
        if (!empty($_POST['nuevas_operaciones'])) {
            // Primero insertar las nuevas operaciones
            $sql = "INSERT IGNORE INTO tb_operaciones (nombre, estado) 
                    VALUES (:nombre, 'Activo')";
            $stmt = $pdo->prepare($sql);
            
            $sql_rel = "INSERT INTO tb_material_operaciones (material_id, operacion_id) 
                       VALUES (:material_id, :operacion_id)";
            $stmt_rel = $pdo->prepare($sql_rel);
            
            foreach ($_POST['nuevas_operaciones'] as $nombre_operacion) {
                // Insertar la nueva operación
                $stmt->execute([':nombre' => trim($nombre_operacion)]);
                $operacion_id = $pdo->lastInsertId();
                
                // Si es 0, significa que ya existía (por el IGNORE)
                if ($operacion_id == 0) {
                    // Obtener el ID de la operación existente
                    $sql_get = "SELECT id FROM tb_operaciones WHERE nombre = :nombre LIMIT 1";
                    $stmt_get = $pdo->prepare($sql_get);
                    $stmt_get->execute([':nombre' => trim($nombre_operacion)]);
                    $operacion = $stmt_get->fetch(PDO::FETCH_ASSOC);
                    $operacion_id = $operacion['id'];
                }
                
                // Crear la relación
                $stmt_rel->execute([
                    ':material_id' => $material_id,
                    ':operacion_id' => $operacion_id
                ]);
            }
        }
        
        // Confirmar la transacción
        $pdo->commit();
        
        // Redirigir con mensaje de éxito
        header("Location: ../view/materiales.php?msg=Material directo guardado correctamente&msg_type=success");
        exit();
        
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $pdo->rollBack();
        
        // Manejar el error
        $error = 'Error al guardar el material directo: ' . $e->getMessage();
        header("Location: ../view/materiales.php?msg=" . urlencode($error) . "&msg_type=danger");
        exit();
    }
} else {
    // Si no es POST, redirigir
    header("Location: ../view/materiales.php");
    exit();
}
?>
