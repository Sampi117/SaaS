<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once('../config/conexion.php');

use TCPDF as TCPDF;

class MYPDF extends TCPDF {
    // Page header
    public function Header() {
        // Logo
        $image_file = '../assets/img/logo.png';
        $this->Image($image_file, 10, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        // Title
        $this->Cell(0, 15, 'FACTURA DE COMPRA', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        // Line break
        $this->Ln(20);
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Obtener el tipo de material y el ID
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!in_array($tipo, ['directo', 'indirecto']) || $id <= 0) {
    die('Parámetros inválidos');
}

// Crear nuevo documento PDF
date_default_timezone_set('America/Bogota');
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configuración del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema de Gestión');
$pdf->SetTitle('Factura de Compra');
$pdf->SetSubject('Factura de Compra');
$pdf->SetKeywords('Factura, Compra, Materiales');

// Eliminar cabecera y pie de página por defecto
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

// Establecer márgenes
$pdf->SetMargins(15, 30, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Añadir una página
$pdf->AddPage();

// Obtener datos de la factura
$factura = [];
$detalles = [];
$subtotal = 0;
$iva = 0;
$total = 0;

if ($tipo === 'directo') {
    // Obtener datos del material directo
    $sql = "SELECT m.*, p.nombre as proveedor_nombre, 
                   CONCAT(p.tipo_documento, ' ', p.numero_documento) as proveedor_nit, 
                   p.direccion as proveedor_direccion, 
                   p.telefono as proveedor_telefono, 
                   p.email as proveedor_email
            FROM tb_materiales_directos m
            LEFT JOIN tb_proveedores p ON m.proveedor_id = p.id
            WHERE m.id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $material = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($material) {
        $factura = [
            'tipo' => 'Material Directo',
            'fecha' => date('Y-m-d H:i:s'),
            'proveedor' => $material['proveedor_nombre'] ?? 'No especificado',
            'nit' => $material['proveedor_nit'] ?? 'No especificado',
            'direccion' => $material['proveedor_direccion'] ?? 'No especificado',
            'telefono' => $material['proveedor_telefono'] ?? 'No especificado',
            'email' => $material['proveedor_email'] ?? 'No especificado'
        ];
        
        $subtotal = $material['costo'] * $material['cantidad'];
        $iva = $subtotal * 0.19;
        $total = $subtotal + $iva;
        
        $detalles[] = [
            'codigo' => $material['id'],
            'descripcion' => $material['nombre'],
            'cantidad' => $material['cantidad'],
            'unidad' => $material['unidad_medida'],
            'valor_unitario' => $material['costo'],
            'valor_total' => $subtotal
        ];
    }
} else {
    // Obtener datos del material indirecto
    $sql = "SELECT m.*, p.nombre as proveedor_nombre, 
                   CONCAT(p.tipo_documento, ' ', p.numero_documento) as proveedor_nit, 
                   p.direccion as proveedor_direccion, 
                   p.telefono as proveedor_telefono, 
                   p.email as proveedor_email
            FROM tb_materiales_indirectos m
            LEFT JOIN tb_proveedores p ON m.proveedor_id = p.id
            WHERE m.id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $material = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($material) {
        $factura = [
            'tipo' => 'Material Indirecto',
            'fecha' => date('Y-m-d H:i:s'),
            'proveedor' => $material['proveedor_nombre'] ?? 'No especificado',
            'nit' => $material['proveedor_nit'] ?? 'No especificado',
            'direccion' => $material['proveedor_direccion'] ?? 'No especificado',
            'telefono' => $material['proveedor_telefono'] ?? 'No especificado',
            'email' => $material['proveedor_email'] ?? 'No especificado'
        ];
        
        $subtotal = $material['costo'] * $material['cantidad'];
        $iva = $subtotal * 0.19;
        $total = $subtotal + $iva;
        
        $detalles[] = [
            'codigo' => $material['id'],
            'descripcion' => $material['nombre'],
            'cantidad' => $material['cantidad'],
            'unidad' => $material['unidad_medida'],
            'valor_unitario' => $material['costo'],
            'valor_total' => $subtotal
        ];
    }
}

if (empty($factura)) {
    die('No se encontró el material especificado');
}

// Contenido de la factura
$html = '<!-- CSS -->
<style>
    .header { font-weight: bold; margin-bottom: 5px; }
    .info { margin-bottom: 15px; }
    table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    th { background-color: #f2f2f2; text-align: left; padding: 8px; border: 1px solid #ddd; }
    td { padding: 8px; border: 1px solid #ddd; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .total-row { font-weight: bold; background-color: #f2f2f2; }
</style>

<!-- Datos de la factura -->
<div class="header">Datos de la Factura</div>
<div class="info">
    <strong>Tipo:</strong> ' . $factura['tipo'] . '<br>
    <strong>Fecha:</strong> ' . date('d/m/Y H:i', strtotime($factura['fecha'])) . '<br>
    <strong>Número de Factura:</strong> FAC-' . str_pad($id, 6, '0', STR_PAD_LEFT) . '
</div>

<!-- Datos del proveedor -->
<div class="header">Datos del Proveedor</div>
<div class="info">
    <strong>Proveedor:</strong> ' . htmlspecialchars($factura['proveedor']) . '<br>
    <strong>NIT:</strong> ' . htmlspecialchars($factura['nit']) . '<br>
    <strong>Dirección:</strong> ' . htmlspecialchars($factura['direccion']) . '<br>
    <strong>Teléfono:</strong> ' . htmlspecialchars($factura['telefono']) . '<br>
    <strong>Email:</strong> ' . htmlspecialchars($factura['email']) . '
</div>

<!-- Detalles de la factura -->
<div class="header">Detalles de la Compra</div>
<table>
    <thead>
        <tr>
            <th>Código</th>
            <th>Descripción</th>
            <th class="text-right">Cantidad</th>
            <th>Unidad</th>
            <th class="text-right">Valor Unitario</th>
            <th class="text-right">Valor Total</th>
        </tr>
    </thead>
    <tbody>';

foreach ($detalles as $detalle) {
    $html .= '<tr>
        <td>' . $detalle['codigo'] . '</td>
        <td>' . htmlspecialchars($detalle['descripcion']) . '</td>
        <td class="text-right">' . number_format($detalle['cantidad'], 2, ',', '.') . '</td>
        <td>' . $detalle['unidad'] . '</td>
        <td class="text-right">$' . number_format($detalle['valor_unitario'], 2, ',', '.') . '</td>
        <td class="text-right">$' . number_format($detalle['valor_total'], 2, ',', '.') . '</td>
    </tr>';
}

$html .= '    <tr class="total-row">
        <td colspan="5" class="text-right"><strong>Subtotal:</strong></td>
        <td class="text-right">$' . number_format($subtotal, 2, ',', '.') . '</td>
    </tr>
    <tr class="total-row">
        <td colspan="5" class="text-right"><strong>IVA (19%):</strong></td>
        <td class="text-right">$' . number_format($iva, 2, ',', '.') . '</td>
    </tr>
    <tr class="total-row">
        <td colspan="5" class="text-right"><strong>Total a Pagar:</strong></td>
        <td class="text-right"><strong>$' . number_format($total, 2, ',', '.') . '</strong></td>
    </tr>
    </tbody>
</table>

<div style="margin-top: 30px; text-align: center; font-size: 10px; color: #666;">
    <p>Este documento es generado automáticamente por el sistema de gestión</p>
    <p>Fecha y hora de generación: ' . date('d/m/Y H:i:s') . '</p>
</div>';

// Escribir el contenido HTML
$pdf->writeHTML($html, true, false, true, false, '');

// Directorio para guardar los PDFs
$pdfDir = __DIR__ . '/../pdf/';
$filename = 'factura_compra_' . $tipo . '_' . $id . '.pdf';
$filepath = $pdfDir . $filename;

// Asegurarse de que el directorio existe y tiene permisos de escritura
if (!file_exists($pdfDir)) {
    mkdir($pdfDir, 0777, true);
}

// Guardar el archivo en el servidor
$pdf->Output($filepath, 'F');

// Verificar si el archivo se creó correctamente
if (file_exists($filepath)) {
    // Configurar las cabeceras para forzar la descarga
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filepath));
    
    // Limpiar el búfer de salida
    ob_clean();
    flush();
    
    // Leer el archivo y enviarlo al navegador
    readfile($filepath);
    
    // Opcional: eliminar el archivo después de la descarga
    // unlink($filepath);
    
    exit;
} else {
    die('Error al generar el archivo PDF');
}
?>
