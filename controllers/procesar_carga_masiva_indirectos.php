<?php
require_once '../vendor/autoload.php';
require_once '../config/conexion.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

// Configuración
$response = [
    'success' => false,
    'message' => '',
    'errors' => [],
    'imported' => 0,
    'failed' => 0,
    'details' => []
];

try {
    // Verificar si se envió un archivo
    if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No se ha subido ningún archivo o hubo un error en la carga.');
    }

    $nombreArchivo = $_FILES['archivo']['name'];
    $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

    // Validar extensión del archivo
    if (!in_array($extension, ['xlsx', 'xls'])) {
        throw new Exception('Formato de archivo no válido. Solo se permiten archivos Excel (.xlsx, .xls)');
    }

    // Cargar el archivo Excel
    $spreadsheet = IOFactory::load($_FILES['archivo']['tmp_name']);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();

    // Eliminar la fila de encabezados
    $headers = array_shift($rows);

    // Validar encabezados
    $expectedHeaders = ['NOMBRE*', 'DESCRIPCION', 'UNIDAD_MEDIDA*', 'CANTIDAD*', 'CANTIDAD_MINIMA', 'PROVEEDOR*', 'COSTO*', 'ESTADO*'];
    
    // Verificar columnas requeridas
    foreach ($expectedHeaders as $header) {
        if (!in_array($header, $headers)) {
            throw new Exception("La columna '$header' es requerida pero no se encontró en el archivo.");
        }
    }

    // Procesar filas
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=Albania_SaaS;charset=utf8", "root", "313878");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();

    try {
        $imported = 0;
        $failed = 0;
        $details = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 porque Excel empieza en 1 y quitamos la fila de encabezados
            $rowData = array_combine($headers, $row);
            $errors = [];

            // Validar campos requeridos
            $requiredFields = ['NOMBRE*', 'UNIDAD_MEDIDA*', 'CANTIDAD*', 'PROVEEDOR*', 'COSTO*', 'ESTADO*'];
            foreach ($requiredFields as $field) {
                if (empty(trim($rowData[$field] ?? ''))) {
                    $errors[] = "El campo {$field} es requerido.";
                }
            }

            // Validar formato de números
            if (!empty($rowData['CANTIDAD*']) && !is_numeric($rowData['CANTIDAD*'])) {
                $errors[] = "El campo CANTIDAD debe ser un número válido.";
            }
            if (!empty($rowData['CANTIDAD_MINIMA']) && !is_numeric($rowData['CANTIDAD_MINIMA'])) {
                $errors[] = "El campo CANTIDAD_MINIMA debe ser un número válido.";
            }
            if (!empty($rowData['COSTO*']) && !is_numeric($rowData['COSTO*'])) {
                $errors[] = "El campo COSTO debe ser un número válido.";
            }

            // Si hay errores, registrar y continuar con la siguiente fila
            if (!empty($errors)) {
                $response['details'][] = [
                    'row' => $rowNumber,
                    'errors' => $errors,
                    'data' => $rowData
                ];
                $response['failed']++;
                continue;
            }

            // Preparar datos para la inserción
            $data = [];
            foreach ($rowData as $key => $value) {
                $data[$key] = trim($value);
            }

            // Convertir a mayúsculas los campos de texto
            $textFields = ['NOMBRE*', 'DESCRIPCION', 'UNIDAD_MEDIDA*', 'PROVEEDOR*', 'ESTADO*'];
            foreach ($textFields as $field) {
                if (isset($data[$field])) {
                    $data[$field] = strtoupper($data[$field]);
                }
            }

            // Obtener el ID del proveedor
            $proveedorId = null;
            if (!empty($data['PROVEEDOR*'])) {
                // Buscar por ID o nombre
                $stmt = $pdo->prepare("SELECT id FROM tb_proveedores WHERE id = ? OR nombre LIKE ? LIMIT 1");
                $stmt->execute([$data['PROVEEDOR*'], "%{$data['PROVEEDOR*']}%"]);
                $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);
                $proveedorId = $proveedor ? $proveedor['id'] : null;

                if (!$proveedorId) {
                    $response['details'][] = [
                        'row' => $rowNumber,
                        'status' => 'error',
                        'message' => "No se encontró el proveedor: {$data['PROVEEDOR*']}",
                        'data' => $rowData
                    ];
                    $response['failed']++;
                    continue;
                }
            }

            try {
                // Insertar en la base de datos
                $sql = "INSERT INTO tb_materiales_indirectos (
                            nombre, descripcion, unidad_medida, cantidad, cantidad_minima, 
                            proveedor_id, costo, estado, fecha_creacion
                        ) VALUES (
                            :nombre, :descripcion, :unidad_medida, :cantidad, :cantidad_minima, 
                            :proveedor_id, :costo, :estado, NOW()
                        )";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nombre' => $data['NOMBRE*'] ?? null,
                    ':descripcion' => $data['DESCRIPCION'] ?? null,
                    ':unidad_medida' => $data['UNIDAD_MEDIDA*'] ?? null,
                    ':cantidad' => $data['CANTIDAD*'] ?? 0,
                    ':cantidad_minima' => $data['CANTIDAD_MINIMA'] ?? 0,
                    ':proveedor_id' => $proveedorId,
                    ':costo' => $data['COSTO*'] ?? 0,
                    ':estado' => $data['ESTADO*'] ?? 'Activo'
                ]);

                $imported++;
                $response['imported']++;
                $response['details'][] = [
                    'row' => $rowNumber,
                    'status' => 'success',
                    'message' => 'Registro importado correctamente',
                    'data' => $rowData
                ];
            } catch (Exception $e) {
                $failed++;
                $response['failed']++;
                $response['details'][] = [
                    'row' => $rowNumber,
                    'status' => 'error',
                    'message' => 'Error al importar registro: ' . $e->getMessage(),
                    'data' => $rowData
                ];
            }
        }

        $pdo->commit();
        $response['success'] = true;
        $response['message'] = "Proceso completado. $imported registros importados, $failed fallidos.";

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    http_response_code(400);
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
