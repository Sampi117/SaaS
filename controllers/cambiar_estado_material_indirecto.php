<?php
header('Content-Type: application/json');

include("../config/conexion.php");

// Verificar que los datos requeridos estén presentes
if (!isset($_POST['id'], $_POST['tipo'], $_POST['estado'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

$id = (int)$_POST['id'];
$tipo = $_POST['tipo'];
$estado = $_POST['estado'];

// Validar el tipo de material
if (!in_array($tipo, ['directo', 'indirecto'])) {
    echo json_encode(['success' => false, 'error' => 'Tipo de material no válido']);
    exit;
}

// Validar el estado
if (!in_array($estado, ['Activo', 'Inactivo'])) {
    echo json_encode(['success' => false, 'error' => 'Estado no válido']);
    exit;
}

try {
    // Determinar la tabla según el tipo de material
    $tabla = $tipo === 'directo' ? 'tb_materiales_directos' : 'tb_materiales_indirectos';
    
    // Actualizar el estado del material
    $sql = "UPDATE $tabla SET estado = :estado WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':estado' => $estado,
        ':id' => $id
    ]);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se pudo actualizar el estado']);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}
?>
