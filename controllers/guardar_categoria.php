<?php
// Habilitar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar en pantalla
ini_set('log_errors', 1);
ini_set('error_log', 'C:/Users/paula/Proyecto SaaS/logs/php_errors.log');

header('Content-Type: text/html; charset=utf-8');
include '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Log de datos recibidos
        error_log('POST recibido: ' . print_r($_POST, true));
        
        // Validar datos de entrada
        $nombre = trim($_POST['nombre'] ?? '');
        $costos_indirectos = floatval($_POST['costos_indirectos'] ?? 0);
        $costos_financieros = floatval($_POST['costos_financieros'] ?? 0);
        $costos_distribucion = floatval($_POST['costos_distribucion'] ?? 0);
        
        // Recibir materiales como JSON
        $materiales = [];
        if (isset($_POST['materiales']) && !empty($_POST['materiales'])) {
            $materiales = json_decode($_POST['materiales'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error al procesar los materiales: ' . json_last_error_msg());
            }
            error_log('Materiales decodificados: ' . print_r($materiales, true));
        }

        // Validaciones
        if (empty($nombre)) {
            throw new Exception('El nombre de la categoría es obligatorio');
        }

        if ($costos_indirectos < 0 || $costos_financieros < 0 || $costos_distribucion < 0) {
            throw new Exception('Los costos no pueden ser negativos');
        }

        $pdo->beginTransaction();

        // Insertar categoría
        $stmt = $pdo->prepare(
            "INSERT INTO categorias 
            (nombre, costos_indirectos, costos_financieros, costos_distribucion, estado, fecha_creacion) 
            VALUES (?, ?, ?, ?, 'Activo', NOW())"
        );
        
        $stmt->execute([
            $nombre, 
            $costos_indirectos, 
            $costos_financieros, 
            $costos_distribucion
        ]);
        
        $categoria_id = $pdo->lastInsertId();
        error_log('Categoría creada con ID: ' . $categoria_id);

        // Insertar materiales relacionados si los hay
        if (!empty($materiales) && is_array($materiales)) {
            $stmtMaterial = $pdo->prepare(
                "INSERT INTO categoria_materiales 
                (id_categoria, id_material, cantidad) 
                VALUES (?, ?, ?)"
            );

            $materialesInsertados = 0;
            foreach ($materiales as $material) {
                $id_material = intval($material['id'] ?? 0);
                $cantidad = floatval($material['cantidad'] ?? 0);
                
                if ($id_material > 0 && $cantidad > 0) {
                    $stmtMaterial->execute([$categoria_id, $id_material, $cantidad]);
                    $materialesInsertados++;
                    error_log("Material insertado - ID: $id_material, Cantidad: $cantidad");
                }
            }
            error_log("Total materiales insertados: $materialesInsertados");
        }

        $pdo->commit();
        error_log('Transacción completada exitosamente');
        
        // Redirigir con mensaje de éxito
        header('Location: ../view/categoria.php?msg=success');
        exit();
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error PDO en guardar_categoria.php: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        header('Location: ../view/categoria.php?msg=error&error=' . urlencode('Error en la base de datos: ' . $e->getMessage()));
        exit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error en guardar_categoria.php: ' . $e->getMessage());
        header('Location: ../view/categoria.php?msg=error&error=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    error_log('Método no POST recibido: ' . $_SERVER['REQUEST_METHOD']);
    header('Location: ../view/categoria.php?msg=error&error=' . urlencode('Método no permitido'));
    exit();
}
?>