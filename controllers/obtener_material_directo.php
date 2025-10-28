<?php
header('Content-Type: application/json');

include("../config/conexion.php");

// Verificar que se haya proporcionado un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'ID de material no válido']);
    exit;
}

$material_id = (int)$_GET['id'];

try {
    // Obtener la información básica del material
    $sql = "SELECT 
                md.*, 
                p.nombre as proveedor_nombre
            FROM tb_materiales_directos md
            LEFT JOIN tb_proveedores p ON md.proveedor_id = p.id
            WHERE md.id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $material_id]);
    $material = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$material) {
        echo json_encode(['error' => 'Material no encontrado']);
        exit;
    }
    
    // Obtener las operaciones asociadas al material
    $sql_operaciones = "SELECT o.id, o.nombre 
                       FROM tb_operaciones o
                       JOIN tb_material_operaciones mo ON o.id = mo.operacion_id
                       WHERE mo.material_id = :material_id";
    
    $stmt_operaciones = $pdo->prepare($sql_operaciones);
    $stmt_operaciones->execute([':material_id' => $material_id]);
    $operaciones = $stmt_operaciones->fetchAll(PDO::FETCH_ASSOC);
    
    // Agregar las operaciones al array del material
    $material['operaciones'] = $operaciones;
    
    // Devolver los datos en formato JSON
    echo json_encode($material);
    
} catch (PDOException $e) {
    // En caso de error, devolver un mensaje de error
    echo json_encode([
        'error' => 'Error al obtener la información del material: ' . $e->getMessage()
    ]);
}
?>
