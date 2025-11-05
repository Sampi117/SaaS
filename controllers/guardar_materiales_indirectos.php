<?php
include("../config/conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST['nombre']);
    $unidad_medida = trim($_POST['unidad_medida']);
    $costo = floatval($_POST['costo']);
    $cantidad = intval($_POST['cantidad']);
    $proveedor_id = !empty($_POST['proveedor_id']) ? intval($_POST['proveedor_id']) : null;

    if ($nombre != "" && $unidad_medida != "" && $costo > 0 && $cantidad >= 0) {
        $sql = "INSERT INTO tb_materiales_indirectos 
                (nombre, unidad_medida, costo, cantidad, proveedor_id, estado) 
                VALUES (:nombre, :unidad_medida, :costo, :cantidad, :proveedor_id, 'Activo')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'unidad_medida' => $unidad_medida,
            'costo' => $costo,
            'cantidad' => $cantidad,
            'proveedor_id' => $proveedor_id
        ]);
        header("Location: ../view/materiales.php?msg=primary");
        exit;
    } else {
        header("Location: ../view/materiales.php?msg=error&error=Datos inválidos");
        exit;
    }
}
