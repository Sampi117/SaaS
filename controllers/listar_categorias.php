<?php
/**
 * Archivo específico para obtener lista de categorías para selects en formularios
 * No afecta los endpoints API existentes
 */
require_once '../config/conexion.php';

$categorias = [];

try {
    $stmt = $pdo->prepare("
        SELECT 
            id as id_categoria, 
            nombre as nombre_categoria 
        FROM categorias 
        WHERE estado = 'Activo' 
        ORDER BY nombre ASC
    ");
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error en listar_categorias.php: ' . $e->getMessage());
    $categorias = [];
}
?>