<?php
/**
 * Archivo específico para obtener lista de materiales directos para selects en formularios
 * No afecta los endpoints API existentes
 */
require_once '../config/conexion.php';

$materiales = [];

try {
    // Listar materiales indirectos activos (usados en fichas técnicas)
    $stmt = $pdo->prepare("
        SELECT 
            id as id_material, 
            nombre as nombre_material, 
            unidad_medida, 
            costo as costo_unitario
        FROM tb_materiales_indirectos
        WHERE estado = 'Activo'
        ORDER BY nombre ASC
    ");
    $stmt->execute();
    $materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error en listar_materiales.php: ' . $e->getMessage());
    $materiales = [];
}
?>