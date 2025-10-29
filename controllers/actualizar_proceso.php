<?php
session_start();
header('Content-Type: application/json');
include_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => 'Error al procesar la solicitud'];
    
    try {
        // Validar datos básicos
        $id = $_POST['id'] ?? '';
        $nombre = trim($_POST['nombre'] ?? '');
        
        if (empty($id) || empty($nombre)) {
            throw new Exception('Todos los campos son requeridos');
        }
        
        // Validar que se hayan seleccionado operaciones
        if (empty($_POST['operaciones']) || !is_array($_POST['operaciones'])) {
            throw new Exception('Debe seleccionar al menos una operación');
        }
        
        // Validar que todos los campos de las operaciones seleccionadas estén completos
        $costos = $_POST['costos'] ?? [];
        $costos_terceros = $_POST['costos_terceros'] ?? [];
        $tiempos = $_POST['tiempos'] ?? [];
        
        foreach ($_POST['operaciones'] as $operacion_id) {
            if (!isset($costos[$operacion_id]) || $costos[$operacion_id] === '') {
                throw new Exception('Todos los campos de costo son requeridos');
            }
            
            if (!isset($costos_terceros[$operacion_id]) || $costos_terceros[$operacion_id] === '') {
                throw new Exception('Todos los campos de costo terceros son requeridos');
            }
            
            if (!isset($tiempos[$operacion_id]) || $tiempos[$operacion_id] === '') {
                throw new Exception('Todos los campos de tiempo son requeridos');
            }
            
            // Validar que sean valores numéricos válidos
            if (!is_numeric($costos[$operacion_id]) || $costos[$operacion_id] < 0) {
                throw new Exception('Los costos deben ser valores numéricos positivos');
            }
            
            if (!is_numeric($costos_terceros[$operacion_id]) || $costos_terceros[$operacion_id] < 0) {
                throw new Exception('Los costos terceros deben ser valores numéricos positivos');
            }
            
            if (!is_numeric($tiempos[$operacion_id]) || $tiempos[$operacion_id] <= 0) {
                throw new Exception('Los tiempos deben ser valores numéricos mayores a 0');
            }
        }
        
        // Calcular totales
        $costo_total = 0;
        $costo_terceros_total = 0;
        $tiempo_total = 0;
        
        foreach ($_POST['operaciones'] as $operacion_id) {
            $costo_total += (float)$costos[$operacion_id];
            $costo_terceros_total += (float)$costos_terceros[$operacion_id];
            $tiempo_total += (float)$tiempos[$operacion_id];
        }
        
        // Iniciar transacción
        $pdo->beginTransaction();
        
        // Actualizar el proceso con los totales calculados
        $stmt = $pdo->prepare("
            UPDATE tb_procesos 
            SET nombre = ?, costo = ?, costo_terceros = ?, tiempo_max_entrega = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $nombre,
            $costo_total,
            $costo_terceros_total,
            $tiempo_total,
            $id
        ]);
        
        // Eliminar operaciones anteriores
        $stmt = $pdo->prepare("DELETE FROM tb_proceso_operaciones WHERE proceso_id = ?");
        $stmt->execute([$id]);
        
        // Insertar las nuevas operaciones del proceso con sus valores individuales
        $stmt = $pdo->prepare("
            INSERT INTO tb_proceso_operaciones 
            (proceso_id, operacion_id, orden, costo, costo_terceros, tiempo_estimado) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $orden = 1;
        foreach ($_POST['operaciones'] as $operacion_id) {
            $costo_operacion = (float)$costos[$operacion_id];
            $costo_terceros_operacion = (float)$costos_terceros[$operacion_id];
            $tiempo_operacion = (float)$tiempos[$operacion_id];
            
            $stmt->execute([
                $id,
                $operacion_id,
                $orden++,
                $costo_operacion,
                $costo_terceros_operacion,
                $tiempo_operacion
            ]);
        }
        
        // Confirmar transacción
        $pdo->commit();
        
        $response = [
            'success' => true,
            'message' => 'Proceso actualizado correctamente',
            'id' => $id,
            'totales' => [
                'costo' => $costo_total,
                'costo_terceros' => $costo_terceros_total,
                'tiempo' => $tiempo_total
            ]
        ];
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

// Si no es POST, redirigir
header('Location: ../view/procesos.php');
exit;