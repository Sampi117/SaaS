<?php
require_once '../config/conexion.php';

$categorias = [];

try {
    $sql = "SELECT 
                id as id_categoria,
                nombre as nombre_categoria,
                estado
            FROM categorias 
            WHERE estado = 'Activo' 
            ORDER BY nombre ASC";
    
    $stmt = $pdo->query($sql);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Categorías cargadas: " . count($categorias));
    
} catch (PDOException $e) {
    error_log('Error en listar_categorias.php: ' . $e->getMessage());
    $categorias = [];
}
?>