<?php
header('Content-Type: application/json');
require_once '../config/conexion.php';

$response = ['success' => false, 'data' => [], 'recordsTotal' => 0, 'recordsFiltered' => 0];

try {
    // Parámetros de búsqueda
    $search = isset($_GET['search']) ? trim($_GET['search']['value']) : '';
    $estado = isset($_GET['estado']) ? (int)$_GET['estado'] : null;
    $id_categoria = isset($_GET['id_categoria']) ? (int)$_GET['id_categoria'] : null;
    
    // Construir la consulta base
    $sql = "SELECT 
                ft.id, 
                ft.referencia, 
                ft.descripcion, 
                c.nombre as categoria, 
                ft.color, 
                ft.estado, 
                ft.fecha_creacion, 
                ft.imagen,
                ft.suela
            FROM fichas_tecnicas ft
            LEFT JOIN categorias c ON ft.id_categoria = c.id
            WHERE 1=1";
    
    $params = [];
    
    // Aplicar filtros
    if (!empty($search)) {
        $sql .= " AND (ft.referencia LIKE ? OR ft.descripcion LIKE ? OR c.nombre LIKE ? OR ft.color LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if ($estado !== null) {
        $sql .= " AND ft.estado = ?";
        $params[] = $estado;
    }
    
    if ($id_categoria) {
        $sql .= " AND ft.id_categoria = ?";
        $params[] = $id_categoria;
    }
    
    // Ordenar
    $sql .= " ORDER BY ft.fecha_creacion DESC, ft.id DESC";
    
    // Ejecutar consulta
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear la respuesta
    foreach ($fichas as &$ficha) {
        $ficha['id'] = (int)$ficha['id'];
        $ficha['estado'] = (int)$ficha['estado'];
        
        // Si no hay descripción, poner guión
        if (empty($ficha['descripcion'])) {
            $ficha['descripcion'] = '-';
        } else {
            // Limitar descripción a 100 caracteres
            if (strlen($ficha['descripcion']) > 100) {
                $ficha['descripcion'] = substr($ficha['descripcion'], 0, 100) . '...';
            }
        }
        
        // Si hay una imagen, construir la URL completa
        if (!empty($ficha['imagen'])) {
            $ficha['imagen_url'] = '../' . $ficha['imagen'];
        } else {
            $ficha['imagen_url'] = null;
        }
    }
    
    $response['success'] = true;
    $response['data'] = $fichas;
    $response['recordsTotal'] = count($fichas);
    $response['recordsFiltered'] = count($fichas);
    
} catch (PDOException $e) {
    $response['message'] = 'Error al obtener las fichas técnicas: ' . $e->getMessage();
    error_log('Error PDO en obtener_fichas_tecnicas.php: ' . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error en obtener_fichas_tecnicas.php: ' . $e->getMessage());
}

echo json_encode($response);
?>