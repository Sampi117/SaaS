<?php
require_once '../vendor/autoload.php';
require_once '../config/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Crear un nuevo objeto Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Obtener lista de proveedores para la validación
$proveedores = [];
try {
    $query = "SELECT id, CONCAT(nombre, ' - ', IFNULL(telefono, 'SIN TELÉFONO')) as nombre 
              FROM tb_proveedores 
              WHERE estado = 'Activo' 
              ORDER BY nombre";
    $stmt = $pdo->query($query);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $proveedores[$row['id']] = $row['nombre'];
    }
    
    $proveedores_list = '"' . implode(',', array_map('addslashes', $proveedores)) . '"';
} catch (PDOException $e) {
    die("Error al obtener proveedores: " . $e->getMessage());
}

// Establecer propiedades del documento
$spreadsheet->getProperties()
    ->setCreator('Sistema de Gestión')
    ->setTitle('Plantilla de Carga Masiva - Materiales Indirectos')
    ->setDescription('Plantilla para carga masiva de materiales indirectos');

// Configurar estilos
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
];

$requiredStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']]
];

// Configurar encabezados
$headers = [
    'A' => 'NOMBRE*',
    'B' => 'DESCRIPCION',
    'C' => 'UNIDAD_MEDIDA*',
    'D' => 'CANTIDAD*',
    'E' => 'CANTIDAD_MINIMA',
    'F' => 'PROVEEDOR*',
    'G' => 'COSTO*',
    'H' => 'ESTADO*'
];

// Aplicar estilos a los encabezados
foreach ($headers as $col => $header) {
    $sheet->setCellValue($col . '1', $header);
    $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
    
    // Marcar campos obligatorios en rojo
    if (strpos($header, '*') !== false) {
        $sheet->getStyle($col . '1')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
    }
}

// Ajustar el ancho de las columnas
foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Agregar datos de ejemplo
$exampleData = [
    ['Tornillos', 'Tornillos de acero inoxidable', 'UNIDAD', '100', '20', '1', '500', 'Activo'],
    ['Pegante', 'Pegante para calzado', 'TUBO', '50', '10', '2', '15000', 'Activo']
];

$row = 2;
foreach ($exampleData as $data) {
    $col = 'A';
    foreach ($data as $value) {
        $sheet->setCellValue($col . $row, $value);
        $col++;
    }
    $row++;
}

// Agregar validación de datos
$validation = $sheet->getDataValidation('F2:F1000');
$validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
$validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
$validation->setAllowBlank(false);
$validation->setShowInputMessage(true);
$validation->setShowErrorMessage(true);
$validation->setShowDropDown(true);
$validation->setErrorTitle('Error en la entrada');
$validation->setError('El valor no está en la lista de proveedores');
$validation->setPromptTitle('Seleccione un proveedor');
$validation->setPrompt('Seleccione un proveedor de la lista');

// Usar una fórmula para la validación de lista
$lastRow = 1000; // Número de filas para la validación
$validation->setFormula1('INDIRECT(\'Proveedores\')');

// Crear una hoja oculta con la lista de proveedores
$proveedoresSheet = $spreadsheet->createSheet();
$proveedoresSheet->setTitle('Proveedores');
$proveedoresSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

// Agregar la lista de proveedores a la hoja oculta
$proveedoresSheet->fromArray($proveedores, null, 'A1');

// Proteger la hoja para evitar modificaciones en la estructura
$sheet->getProtection()->setSheet(true);

// Configurar la hoja activa
$spreadsheet->setActiveSheetIndex(0);

// Configurar cabeceras para la descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Plantilla_Carga_Masiva_Indirectos.xlsx"');
header('Cache-Control: max-age=0');

// Guardar el archivo
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

exit;
