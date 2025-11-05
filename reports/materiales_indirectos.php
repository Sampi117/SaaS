<?php
require_once '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

include_once '../includes/header.php';
include_once '../includes/sidebar.php';
include_once '../config/conexion.php';

// Procesar la descarga de la plantilla
if (isset($_GET['descargar_plantilla'])) {
    // Crear un nuevo objeto Spreadsheet

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

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
        ['Tornillos', 'Tornillos de acero inoxidable', 'UNIDAD', '100', '20', 'Proveedor Ejemplo', '500', 'Activo'],
        ['Pegante', 'Pegante para calzado', 'TUBO', '50', '10', 'Otro Proveedor', '15000', 'Activo']
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

    // Configurar cabeceras para la descarga
    $filename = 'Plantilla_Carga_Masiva_Indirectos_' . date('Y-m-d') . '.xlsx';
    
    // Limpiar cualquier salida anterior
    if (ob_get_contents()) {
        ob_clean();
    }
    
    // Forzar la descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Guardar el archivo
    $writer = new Xlsx($spreadsheet);
    
    // Deshabilitar la compresión para evitar problemas
    $writer->setPreCalculateFormulas(false);
    
    // Guardar en un buffer primero
    ob_start();
    $writer->save('php://output');
    $content = ob_get_clean();
    
    // Limpiar cualquier salida adicional
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Enviar el contenido
    echo $content;
    exit;
}

// Procesar el archivo subido
$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
    require_once '../vendor/autoload.php';
    require_once '../controllers/procesar_carga_masiva_indirectos.php';
    exit;
}
?>

<div class="main-content p-4">
    <div class="container-fluid"><br><br><br>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark">Carga Masiva de Materiales Indirectos</h2>
            <a href="../view/materiales.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a Materiales
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-upload me-2"></i>Instrucciones para la Carga Masiva</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5><i class="bi bi-info-circle-fill me-2"></i>Requisitos del Archivo Excel</h5>
                    <ul class="mb-0">
                        <li>El archivo debe estar en formato <strong>Excel (.xlsx)</strong></li>
                        <li>La primera fila debe contener los encabezados exactos como se muestra en la plantilla</li>
                        <li>No modifique los nombres de las columnas</li>
                        <li>Los campos marcados con (*) son obligatorios</li>
                        <li>Los valores numéricos no deben contener separadores de miles y usar punto (.) como separador decimal</li>
                    </ul>
                </div>

                <h5 class="mt-4">Estructura del Archivo</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Campo</th>
                                <th>Tipo</th>
                                <th>Requerido</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>nombre*</td>
                                <td>Texto</td>
                                <td>Sí</td>
                                <td>Nombre del material indirecto</td>
                            </tr>
                            <tr>
                                <td>unidad_medida*</td>
                                <td>Texto</td>
                                <td>Sí</td>
                                <td>Unidad de medida (ej: Unidad, Metro, Litro, etc.)</td>
                            </tr>
                            <tr>
                                <td>costo*</td>
                                <td>Número</td>
                                <td>Sí</td>
                                <td>Costo unitario (sin símbolos de moneda)</td>
                            </tr>
                            <tr>
                                <td>cantidad*</td>
                                <td>Número</td>
                                <td>Sí</td>
                                <td>Cantidad en inventario</td>
                            </tr>
                            <tr>
                                <td>proveedor_id</td>
                                <td>Número</td>
                                <td>No</td>
                                <td>ID del proveedor (opcional)</td>
                            </tr>
                            <tr>
                                <td>estado*</td>
                                <td>Texto</td>
                                <td>Sí</td>
                                <td>Activo o Inactivo</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php if ($mensaje): ?>
                    <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show mt-3" role="alert">
                        <?= $mensaje ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="mt-4">
                    <a href="?descargar_plantilla=1" class="btn btn-success me-2">
                        <i class="bi bi-download me-2"></i>Descargar Plantilla
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCargaMasiva">
                        <i class="bi bi-upload me-2"></i>Subir Archivo Excel
                    </button>
                </div>
            </div>
        </div><br><br>
    </div>
</div> <br><br>

<!-- Modal Carga Masiva -->
<div class="modal fade" id="modalCargaMasiva" tabindex="-1" aria-labelledby="modalCargaMasivaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCargaMasivaLabel">Cargar Archivo Excel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCargaMasiva" action="" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="archivo" class="form-label">Seleccione el archivo Excel</label>
                        <input class="form-control" type="file" id="archivo" name="archivo" accept=".xlsx, .xls" required>
                        <div class="form-text">
                            <a href="?descargar_plantilla=1" class="text-decoration-none">
                                <i class="bi bi-download"></i> Descargar plantilla de ejemplo
                            </a>
                        </div>
                    </div>
                    <div id="resultadoCarga" class="d-none">
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm me-2" role="status" id="spinnerCarga">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <span id="mensajeCarga">Procesando archivo, por favor espere...</span>
                            </div>
                        </div>
                        <div id="detallesCarga" class="mt-3"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnProcesarCarga">Procesar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Manejar el envío del formulario de carga masiva
document.getElementById('formCargaMasiva').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    const resultadoDiv = document.getElementById('resultadoCarga');
    const detallesDiv = document.getElementById('detallesCarga');
    const mensajeCarga = document.getElementById('mensajeCarga');
    const btnProcesar = document.getElementById('btnProcesarCarga');
    const spinner = document.getElementById('spinnerCarga');
    
    // Mostrar resultados y spinner
    resultadoDiv.classList.remove('d-none');
    btnProcesar.disabled = true;
    spinner.classList.remove('d-none');
    mensajeCarga.textContent = 'Procesando archivo, por favor espere...';
    detallesDiv.innerHTML = '';
    
    // Enviar archivo al servidor
    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Ocultar spinner
        spinner.classList.add('d-none');
        
        if (data.success) {
            mensajeCarga.innerHTML = `<strong>¡Carga completada!</strong> ${data.message}`;
            
            // Mostrar resumen
            let html = `
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i> ${data.imported} registros importados correctamente
                </div>
            `;
            
            if (data.failed > 0) {
                html += `
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i> ${data.failed} registros no se pudieron importar
                    </div>
                `;
            }
            
            // Mostrar detalles de errores si los hay
            if (data.failed > 0) {
                html += '<h6 class="mt-3">Detalles de errores:</h6><div class="table-responsive"><table class="table table-sm table-bordered">';
                html += '<thead><tr><th>Fila</th><th>Error</th><th>Datos</th></tr></thead><tbody>';
                
                data.details.forEach(detail => {
                    if (detail.status === 'error') {
                        html += `<tr>
                            <td>${detail.row}</td>
                            <td>${detail.message}</td>
                            <td>${JSON.stringify(detail.data)}</td>
                        </tr>`;
                    }
                });
                
                html += '</tbody></table></div>';
            }
            
            detallesDiv.innerHTML = html;
            
            // Recargar la página después de 3 segundos si hay éxito
            if (data.imported > 0) {
                setTimeout(() => {
                    window.location.href = 'materiales.php';
                }, 3000);
            }
            
        } else {
            mensajeCarga.innerHTML = `<strong>Error:</strong> ${data.message}`;
            detallesDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        spinner.classList.add('d-none');
        mensajeCarga.innerHTML = '<strong>Error:</strong> Ocurrió un error al procesar el archivo';
        detallesDiv.innerHTML = '<div class="alert alert-danger">' + error.message + '</div>';
    })
    .finally(() => {
        btnProcesar.disabled = false;
    });
});
</script>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
