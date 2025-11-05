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
    // Obtener la información básica del material indirecto
    $sql = "SELECT 
                mi.*, 
                p.nombre as proveedor_nombre
            FROM tb_materiales_indirectos mi
            LEFT JOIN tb_proveedores p ON mi.proveedor_id = p.id
            WHERE mi.id = :id";
    
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
    
    // Si el material está en estado 'Devolución', obtener los detalles de la devolución
    if ($material['estado'] === 'Devolución') {
        $sql_devolucion = "SELECT * FROM tb_devoluciones_materiales 
                          WHERE material_id = :material_id AND tipo_material = 'indirecto'
                          ORDER BY fecha_devolucion DESC LIMIT 1";
        
        $stmt_devolucion = $pdo->prepare($sql_devolucion);
        $stmt_devolucion->execute([':material_id' => $material_id]);
        $devolucion = $stmt_devolucion->fetch(PDO::FETCH_ASSOC);
        
        if ($devolucion) {
            $material['devolucion'] = $devolucion;
        }
    }
    
    // Agregar las operaciones al array del material
    $material['operaciones'] = $operaciones;
    
    // Devolver los datos en formato JSON
    echo json_encode($material, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    // En caso de error, devolver un mensaje de error
    echo json_encode([
        'error' => 'Error al obtener la información del material: ' . $e->getMessage()
    ]);
}
?>
