<?php
// Habilitar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/conexion.php';

// Verificar si es una solicitud AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Acceso denegado']));
}

// Establecer cabeceras para JSON
header('Content-Type: application/json; charset=utf-8');

// Función para enviar respuesta JSON
function sendJsonResponse($success, $message = '', $data = []) {
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data
    ];
    
    // Registrar en el log del servidor
    error_log('Respuesta JSON: ' . json_encode($response));
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    sendJsonResponse(false, 'Método no permitido');
}

try {
    // Validar que el usuario esté autenticado
    if (!isset($_SESSION['user_id'])) {
        error_log('Error: Usuario no autenticado');
        http_response_code(401);
        sendJsonResponse(false, 'No se ha iniciado sesión');
    }

    // Validar datos requeridos
    $requiredFields = ['id', 'tipo_material', 'razon'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        $errorMsg = 'Faltan campos requeridos: ' . implode(', ', $missingFields);
        error_log('Error de validación: ' . $errorMsg);
        http_response_code(400);
        sendJsonResponse(false, $errorMsg);
    }

    // Obtener y validar datos
    $material_id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $tipo_material = in_array($_POST['tipo_material'], ['directo', 'indirecto']) ? $_POST['tipo_material'] : null;
    $razon = trim(htmlspecialchars($_POST['razon'], ENT_QUOTES, 'UTF-8'));
    $usuario_id = $_SESSION['user_id'];
    $fecha_devolucion = date('Y-m-d H:i:s');
    
    // Validar datos
    $errores = [];
    
    if (!$material_id || $material_id <= 0) {
        $errores[] = 'ID de material no válido';
    }
    
    if (!$tipo_material) {
        $errores[] = 'Tipo de material no válido (debe ser directo o indirecto)';
    }
    
    if (empty($razon)) {
        $errores[] = 'La razón de la devolución es obligatoria';
    } elseif (strlen($razon) > 1000) {
        $errores[] = 'La razón no puede tener más de 1000 caracteres';
    }
    
    if (count($errores) > 0) {
        error_log('Errores de validación: ' . implode(', ', $errores));
        http_response_code(400);
        sendJsonResponse(false, 'Error de validación: ' . implode('. ', $errores));
    }

    // Iniciar transacción
    $pdo->beginTransaction();
    
    try {
        // 1. Verificar que el material existe
        $tabla = ($tipo_material === 'directo') ? 'tb_materiales_directos' : 'tb_materiales_indirectos';
        $sql_check = "SELECT id, nombre, estado FROM $tabla WHERE id = :id LIMIT 1";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([':id' => $material_id]);
        $material = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        if (!$material) {
            throw new Exception('El material especificado no existe o no se pudo encontrar');
        }
        
        // Obtener información adicional del material
        $sql_material = "SELECT * FROM $tabla WHERE id = :id LIMIT 1";
        $stmt_material = $pdo->prepare($sql_material);
        $stmt_material->execute([':id' => $material_id]);
        $material = $stmt_material->fetch(PDO::FETCH_ASSOC);
        
        if (!$material) {
            throw new Exception('No se pudo obtener la información del material');
        }
        
        // Configurar valores por defecto sin información de usuario
        $usuario_id = 0; // Valor por defecto
        $usuario_nombre = 'Sistema'; // Valor por defecto
        $cantidad = 1; // Valor por defecto
        $estado_anterior = $material['estado'];
        
        // 2. Insertar registro de devolución en tb_devoluciones_materiales
        $sql = "INSERT INTO tb_devoluciones_materiales 
                (material_id, tipo_material, cantidad, razon, fecha_devolucion, 
                 usuario_id, usuario_nombre, estado_anterior) 
                VALUES (:material_id, :tipo_material, :cantidad, :razon, :fecha_devolucion, 
                        :usuario_id, :usuario_nombre, :estado_anterior)";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':material_id' => $material_id,
            ':tipo_material' => $tipo_material,
            ':cantidad' => $cantidad,
            ':razon' => $razon,
            ':fecha_devolucion' => $fecha_devolucion,
            ':usuario_id' => $usuario_id,
            ':usuario_nombre' => $usuario_nombre,
            ':estado_anterior' => $estado_anterior
        ]);
        
        if (!$result) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception('Error al insertar registro de devolución: ' . $errorInfo[2]);
        }
        
        $devolucion_id = $pdo->lastInsertId();
        
        // 3. Actualizar estado del material a 'Devolución'
        $sql_update = "UPDATE $tabla SET estado = 'Devolución' WHERE id = :id";
        $stmt_update = $pdo->prepare($sql_update);
        $update_result = $stmt_update->execute([':id' => $material_id]);
        
        if ($stmt_update->rowCount() === 0) {
            throw new Exception('No se pudo actualizar el estado del material');
        }
        
        // Confirmar la transacción
        $pdo->commit();
        
        // Registrar éxito
        error_log(sprintf(
            "Devolución registrada - ID: %d, Material ID: %d, Tipo: %s, Usuario: %d",
            $devolucion_id,
            $material_id,
            $tipo_material,
            $usuario_id
        ));
        
        // Enviar respuesta exitosa
        sendJsonResponse(
            true, 
            'Devolución registrada correctamente',
            [
                'id' => $devolucion_id,
                'material_id' => $material_id,
                'tipo_material' => $tipo_material,
                'fecha' => $fecha_devolucion,
                'material_nombre' => $material['nombre']
            ]
        );
        
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
    
} catch (Exception $e) {
    // Registrar el error
    error_log('Error en procesar_devolucion.php: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    // Determinar código de estado HTTP apropiado
    $httpCode = 500;
    $errorMessage = $e->getMessage();
    
    if (strpos($errorMessage, 'no existe') !== false || 
        strpos($errorMessage, 'no válido') !== false) {
        $httpCode = 400;
    }
    
    http_response_code($httpCode);
    sendJsonResponse(false, 'Error al procesar la devolución: ' . $errorMessage);
}
