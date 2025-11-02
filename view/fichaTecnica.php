<?php
include_once '../includes/header.php';
include_once '../includes/sidebar.php';
include_once '../controllers/listar_categorias.php';
include_once '../controllers/listar_procesos.php';
include_once '../controllers/listar_materiales.php';
?>

<div class="main-content p-4">
    <div class="container-fluid">
        <br><br><br>
        <!-- Título principal -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark">Ficha Técnica</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevaFichaModal">
                <i class="fas fa-plus"></i> Añadir Ficha Técnica
            </button>
        </div>

        <!-- Tabla de fichas técnicas -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaFichas" class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Referencia</th>
                                <th>Descripción</th>
                                <th>Categoría</th>
                                <th>Color</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Los datos se cargarán mediante AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para nueva ficha técnica -->
<div class="modal fade" id="nuevaFichaModal" tabindex="-1" aria-labelledby="nuevaFichaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="nuevaFichaModalLabel">Nueva Ficha Técnica</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formFichaTecnica" enctype="multipart/form-data">
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="fichaTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="datos-basicos-tab" data-bs-toggle="tab" data-bs-target="#datos-basicos" type="button" role="tab">Datos Básicos</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tallas-tab" data-bs-toggle="tab" data-bs-target="#tallas" type="button" role="tab">Tallas</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="procesos-tab" data-bs-toggle="tab" data-bs-target="#procesos" type="button" role="tab">Procesos</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="materiales-tab" data-bs-toggle="tab" data-bs-target="#materiales" type="button" role="tab">Materiales</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content p-3 border border-top-0 rounded-bottom" id="fichaTabsContent">
                        <!-- Pestaña de Datos Básicos -->
                        <div class="tab-pane fade show active" id="datos-basicos" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="referencia" class="form-label">Referencia <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="referencia" name="referencia" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="id_categoria" class="form-label">Categoría <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_categoria" name="id_categoria" required>
                                        <option value="">Seleccione una categoría</option>
                                        <?php foreach ($categorias as $categoria): ?>
                                            <option value="<?php echo $categoria['id_categoria']; ?>">
                                                <?php echo htmlspecialchars($categoria['nombre_categoria']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="color" class="form-label">Color <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="color" name="color" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="suela" class="form-label">Suela <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="suela" name="suela" required>
                                </div>
                                <div class="col-12">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="imagen" class="form-label">Imagen del producto</label>
                                    <input class="form-control" type="file" id="imagen" name="imagen" accept="image/*">
                                    <small class="text-muted">Formatos permitidos: JPG, PNG, GIF (Máx. 2MB)</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="fecha_creacion" class="form-label">Fecha de Creación <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha_creacion" name="fecha_creacion" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pestaña de Tallas -->
                        <div class="tab-pane fade" id="tallas" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Género <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="genero" id="genero_hombre" value="hombre" checked>
                                    <label class="form-check-label" for="genero_hombre">Hombre</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="genero" id="genero_mujer" value="mujer">
                                    <label class="form-check-label" for="genero_mujer">Mujer</label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Rango de Tallas <span class="text-danger">*</span></label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="talla_inicio" class="form-label">Talla Inicial</label>
                                        <input type="number" class="form-control" id="talla_inicio" name="talla_inicio" min="30" max="50" step="1" value="36" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="talla_fin" class="form-label">Talla Final</label>
                                        <input type="number" class="form-control" id="talla_fin" name="talla_fin" min="30" max="50" step="1" value="42" required>
                                    </div>
                                </div>
                                <small class="text-muted">Las tallas se generarán automáticamente en el rango especificado</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Vista Previa de Tallas</label>
                                <div id="tallasPreview" class="d-flex flex-wrap gap-2 p-3 border rounded bg-light">
                                    <span class="badge bg-secondary">36, 37, 38, 39, 40, 41, 42</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- CORRECCIÓN TABLA DE PROCESOS -->
                        <div class="tab-pane fade" id="procesos" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="25%">Proceso</th>
                                            <th width="18%">Mano de Obra</th>
                                            <th width="18%">% Liquidación</th>
                                            <th width="18%">Total</th>
                                            <th width="10%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="procesosBody">
                                        <tr>
                                            <td>
                                                <select class="form-select form-select-sm proceso-select" name="procesos[0][id_proceso]" required>
                                                    <option value="">Seleccione un proceso</option>
                                                    <?php foreach ($procesos as $proceso): ?>
                                                        <option value="<?= $proceso['id_proceso'] ?>" 
                                                                data-costo="<?= number_format($proceso['costo_mano_obra'], 2, '.', '') ?>">
                                                            <?= htmlspecialchars($proceso['nombre_proceso']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm mano-obra" 
                                                    name="procesos[0][mano_obra]" step="0.01" readonly>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" class="form-control liquidacion" 
                                                        name="procesos[0][liquidacion]" step="0.01" value="0" min="0">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm total-proceso" 
                                                    name="procesos[0][total]" step="0.01" readonly>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-success btn-agregar-proceso" title="Agregar proceso">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">Total Procesos:</td>
                                            <td id="totalProcesos" class="fw-bold">$0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <!-- Costos Fijos -->
                            <div class="card mt-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Costos Fijos</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="costos_indirectos" class="form-label">Costos Indirectos</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control costo-fijo" id="costos_indirectos" 
                                                    name="costos_indirectos" step="0.01" value="0" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="costos_financieros" class="form-label">Costos Financieros</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control costo-fijo" id="costos_financieros" 
                                                    name="costos_financieros" step="0.01" value="0" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="costos_distribucion" class="form-label">Costos de Distribución</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control costo-fijo" id="costos_distribucion" 
                                                    name="costos_distribucion" step="0.01" value="0" min="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CORRECCIÓN TABLA DE MATERIALES -->
                        <div class="tab-pane fade" id="materiales" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="15%">Material</th>
                                            <th width="18%">Descripción</th>
                                            <th width="10%">Unidad</th>
                                            <th width="12%">Costo Unit.</th>
                                            <th width="10%">Cantidad</th>
                                            <th width="10%">Ancho (cm)</th>
                                            <th width="10%">Alto (cm)</th>
                                            <th width="12%">Total</th>
                                            <th width="5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="materialesBody">
                                        <tr>
                                            <td>
                                                <select class="form-select form-select-sm material-select" 
                                                        name="materiales[0][id_material]" required>
                                                    <option value="">Seleccione</option>
                                                    <?php foreach ($materiales as $material): ?>
                                                        <option value="<?= $material['id_material'] ?>" 
                                                                data-descripcion="<?= htmlspecialchars($material['descripcion']) ?>"
                                                                data-unidad="<?= htmlspecialchars($material['unidad_medida']) ?>"
                                                                data-costo="<?= number_format($material['costo_unitario'], 2, '.', '') ?>">
                                                            <?= htmlspecialchars($material['nombre_material']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td><input type="text" class="form-control form-control-sm material-descripcion" readonly></td>
                                            <td><input type="text" class="form-control form-control-sm material-unidad" readonly></td>
                                            <td><input type="number" class="form-control form-control-sm material-costo" step="0.01" readonly></td>
                                            <td><input type="number" class="form-control form-control-sm material-cantidad" 
                                                    name="materiales[0][cantidad]" step="0.01" min="0.01" value="1"></td>
                                            <td><input type="number" class="form-control form-control-sm material-ancho" 
                                                    name="materiales[0][ancho]" step="0.01" min="0" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm material-alto" 
                                                    name="materiales[0][alto]" step="0.01" min="0" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm material-total" step="0.01" readonly></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-success btn-agregar-material" title="Agregar material">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="7" class="text-end fw-bold">Total Materiales:</td>
                                            <td id="totalMateriales" class="fw-bold">$0.00</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="7" class="text-end fw-bold">Total Procesos:</td>
                                            <td id="totalProcesosResumen" class="fw-bold">$0.00</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="7" class="text-end fw-bold">Total Costos Fijos:</td>
                                            <td id="totalCostosFijos" class="fw-bold">$0.00</td>
                                            <td></td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td colspan="7" class="text-end fw-bold">Costo Total del Producto:</td>
                                            <td id="costoTotalProducto" class="fw-bold">$0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Ficha Técnica</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ver ficha técnica -->
<div class="modal fade" id="verFichaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detalle de Ficha Técnica</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detalleFicha">
                <!-- Los detalles se cargarán aquí -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    console.log('Inicializando Ficha Técnica...');
    
    // Inicializar DataTable
    var table = $('#tablaFichas').DataTable({
        ajax: {
            url: '../controllers/obtener_fichas_tecnicas.php',
            dataSrc: 'data'
        },
        columns: [
            { data: 'referencia' },
            { data: 'descripcion', defaultContent: '-' },
            { data: 'categoria' },
            { data: 'color' },
            { 
                data: 'estado',
                render: function(data) {
                    return data == 1 ? 
                        '<span class="badge bg-success">Activo</span>' : 
                        '<span class="badge bg-secondary">Inactivo</span>';
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-info btn-ver" data-id="${row.id}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-warning btn-editar" data-id="${row.id}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn ${row.estado == 1 ? 'btn-danger' : 'btn-success'} btn-estado" 
                                    data-id="${row.id}" data-estado="${row.estado}">
                                <i class="fas ${row.estado == 1 ? 'fa-times' : 'fa-check'}"></i>
                            </button>
                        </div>
                    `;
                },
                orderable: false
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        }
    });
    
    // Verificar que los procesos tengan el atributo data-costo correcto
    console.log('=== VERIFICANDO PROCESOS ===');
    $('.proceso-select option').each(function() {
        const costo = $(this).data('costo');
        if (costo !== undefined && $(this).val()) {
            console.log('Proceso:', $(this).text().trim(), 'Costo:', costo);
        }
    });
    
    // Verificar que los materiales tengan los atributos correctos
    console.log('=== VERIFICANDO MATERIALES ===');
    $('.material-select option').each(function() {
        if ($(this).val()) {
            console.log('Material:', $(this).text().trim(), {
                descripcion: $(this).data('descripcion'),
                unidad: $(this).data('unidad'),
                costo: $(this).data('costo')
            });
        }
    });

    // ========== FUNCIONES DE TALLAS ==========
    function actualizarVistaPreviewTallas() {
        const inicio = parseInt($('#talla_inicio').val()) || 36;
        const fin = parseInt($('#talla_fin').val()) || 42;
        
        if (inicio > fin) {
            $('#tallasPreview').html('<span class="text-danger">La talla inicial debe ser menor que la final</span>');
            return;
        }
        
        let tallas = [];
        for (let i = inicio; i <= fin; i++) {
            tallas.push(i);
        }
        
        $('#tallasPreview').html('<span class="badge bg-secondary">' + tallas.join(', ') + '</span>');
    }
    
    $('#talla_inicio, #talla_fin').on('change input', actualizarVistaPreviewTallas);
    
    $('input[name="genero"]').change(function() {
        if ($(this).val() === 'hombre') {
            $('#talla_inicio').val(36);
            $('#talla_fin').val(45);
        } else {
            $('#talla_inicio').val(34);
            $('#talla_fin').val(42);
        }
        actualizarVistaPreviewTallas();
    });

    // ========== FUNCIONES DE PROCESOS ==========
    
    // Calcular total de un proceso
    function calcularTotalProceso($row) {
        const manoObra = parseFloat($row.find('.mano-obra').val()) || 0;
        const liquidacion = parseFloat($row.find('.liquidacion').val()) || 0;
        const total = manoObra * (1 + (liquidacion / 100));
        
        console.log('Calculando proceso:', { manoObra, liquidacion, total });
        
        $row.find('.total-proceso').val(total.toFixed(2));
        return total;
    }
    
    // Calcular total de todos los procesos
    function calcularTotalProcesos() {
        let total = 0;
        $('#procesosBody tr').each(function() {
            total += calcularTotalProceso($(this));
        });
        
        console.log('Total procesos:', total);
        
        $('#totalProcesos').text('$' + total.toFixed(2));
        $('#totalProcesosResumen').text('$' + total.toFixed(2));
        calcularCostoTotal();
        
        return total;
    }
    
    // Actualizar mano de obra al seleccionar proceso
    $(document).on('change', '.proceso-select', function() {
        const $option = $(this).find('option:selected');
        const costo = parseFloat($option.data('costo')) || 0;
        const $row = $(this).closest('tr');
        
        console.log('==== PROCESO SELECCIONADO ====');
        console.log('Nombre:', $option.text().trim());
        console.log('Costo desde data-costo:', costo);
        console.log('Valor de la opción:', $option.val());
        
        $row.find('.mano-obra').val(costo.toFixed(2));
        calcularTotalProceso($row);
        calcularTotalProcesos();
    });
    
    // Actualizar total al cambiar % de liquidación
    $(document).on('input', '.liquidacion', function() {
        calcularTotalProceso($(this).closest('tr'));
        calcularTotalProcesos();
    });
    
    // Agregar fila de proceso
    $(document).on('click', '.btn-agregar-proceso', function() {
        const index = $('#procesosBody tr').length;
        const newRow = `
            <tr>
                <td>
                    <select class="form-select form-select-sm proceso-select" name="procesos[${index}][id_proceso]" required>
                        <option value="">Seleccione un proceso</option>
                        <?php foreach ($procesos as $proceso): ?>
                            <option value="<?= $proceso['id_proceso'] ?>" 
                                    data-costo="<?= number_format($proceso['costo_mano_obra'], 2, '.', '') ?>">
                                <?= htmlspecialchars($proceso['nombre_proceso']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm mano-obra" name="procesos[${index}][mano_obra]" step="0.01" readonly>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control liquidacion" name="procesos[${index}][liquidacion]" step="0.01" value="0" min="0">
                        <span class="input-group-text">%</span>
                    </div>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm total-proceso" name="procesos[${index}][total]" step="0.01" readonly>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-eliminar-proceso">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#procesosBody').append(newRow);
    });
    
    // Eliminar fila de proceso
    $(document).on('click', '.btn-eliminar-proceso', function() {
        if ($('#procesosBody tr').length > 1) {
            $(this).closest('tr').remove();
            calcularTotalProcesos();
        } else {
            Swal.fire('Atención', 'Debe mantener al menos un proceso', 'warning');
        }
    });

    // ========== FUNCIONES DE MATERIALES ==========
    
    // Calcular total de un material
    function calcularTotalMaterial($row) {
        const costo = parseFloat($row.find('.material-costo').val()) || 0;
        const cantidad = parseFloat($row.find('.material-cantidad').val()) || 0;
        const ancho = parseFloat($row.find('.material-ancho').val()) || 0;
        const alto = parseFloat($row.find('.material-alto').val()) || 0;
        
        let area = 1;
        if (ancho > 0 && alto > 0) {
            area = (ancho * alto) / 10000; // Convertir cm² a m²
        }
        
        const total = costo * cantidad * area;
        
        console.log('Calculando material:', { costo, cantidad, ancho, alto, area, total });
        
        $row.find('.material-total').val(total.toFixed(2));
        return total;
    }
    
    // Calcular total de todos los materiales
    function calcularTotalMateriales() {
        let total = 0;
        $('#materialesBody tr').each(function() {
            total += calcularTotalMaterial($(this));
        });
        
        console.log('Total materiales:', total);
        
        $('#totalMateriales').text('$' + total.toFixed(2));
        calcularCostoTotal();
        return total;
    }
    
    // Actualizar información del material al seleccionar
    $(document).on('change', '.material-select', function() {
        const $option = $(this).find('option:selected');
        const $row = $(this).closest('tr');
        
        const descripcion = $option.data('descripcion') || '';
        const unidad = $option.data('unidad') || '';
        const costo = parseFloat($option.data('costo')) || 0;
        
        console.log('==== MATERIAL SELECCIONADO ====');
        console.log('Nombre:', $option.text().trim());
        console.log('Descripción:', descripcion);
        console.log('Unidad:', unidad);
        console.log('Costo:', costo);
        
        $row.find('.material-descripcion').val(descripcion);
        $row.find('.material-unidad').val(unidad);
        $row.find('.material-costo').val(costo.toFixed(2));
        
        calcularTotalMaterial($row);
        calcularTotalMateriales();
    });
    
    // Actualizar total al cambiar cantidad, ancho o alto
    $(document).on('input', '.material-cantidad, .material-ancho, .material-alto', function() {
        calcularTotalMaterial($(this).closest('tr'));
        calcularTotalMateriales();
    });
    
    // Agregar fila de material
    $(document).on('click', '.btn-agregar-material', function() {
        const index = $('#materialesBody tr').length;
        const newRow = `
            <tr>
                <td>
                    <select class="form-select form-select-sm material-select" name="materiales[${index}][id_material]" required>
                        <option value="">Seleccione</option>
                        <?php foreach ($materiales as $material): ?>
                            <option value="<?= $material['id_material'] ?>" 
                                    data-descripcion="<?= htmlspecialchars($material['descripcion']) ?>"
                                    data-unidad="<?= htmlspecialchars($material['unidad_medida']) ?>"
                                    data-costo="<?= number_format($material['costo_unitario'], 2, '.', '') ?>">
                                <?= htmlspecialchars($material['nombre_material']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="text" class="form-control form-control-sm material-descripcion" readonly></td>
                <td><input type="text" class="form-control form-control-sm material-unidad" readonly></td>
                <td><input type="number" class="form-control form-control-sm material-costo" step="0.01" readonly></td>
                <td><input type="number" class="form-control form-control-sm material-cantidad" name="materiales[${index}][cantidad]" step="0.01" min="0.01" value="1"></td>
                <td><input type="number" class="form-control form-control-sm material-ancho" name="materiales[${index}][ancho]" step="0.01" min="0" value="0"></td>
                <td><input type="number" class="form-control form-control-sm material-alto" name="materiales[${index}][alto]" step="0.01" min="0" value="0"></td>
                <td><input type="number" class="form-control form-control-sm material-total" step="0.01" readonly></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-eliminar-material">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#materialesBody').append(newRow);
    });
    
    // Eliminar fila de material
    $(document).on('click', '.btn-eliminar-material', function() {
        if ($('#materialesBody tr').length > 1) {
            $(this).closest('tr').remove();
            calcularTotalMateriales();
        } else {
            Swal.fire('Atención', 'Debe mantener al menos un material', 'warning');
        }
    });

    // ========== CÁLCULO DE COSTOS ==========
    
    function calcularCostoTotal() {
        const totalMateriales = parseFloat($('#totalMateriales').text().replace('$', '').replace(',', '')) || 0;
        const totalProcesos = parseFloat($('#totalProcesos').text().replace('$', '').replace(',', '')) || 0;
        const costoIndirecto = parseFloat($('#costos_indirectos').val()) || 0;
        const costoFinanciero = parseFloat($('#costos_financieros').val()) || 0;
        const costoDistribucion = parseFloat($('#costos_distribucion').val()) || 0;
        
        const totalCostosFijos = costoIndirecto + costoFinanciero + costoDistribucion;
        const costoTotal = totalMateriales + totalProcesos + totalCostosFijos;
        
        console.log('Calculando costo total:', {
            totalMateriales,
            totalProcesos,
            totalCostosFijos,
            costoTotal
        });
        
        $('#totalCostosFijos').text('$' + totalCostosFijos.toFixed(2));
        $('#costoTotalProducto').text('$' + costoTotal.toFixed(2));
    }
    
    $(document).on('input', '.costo-fijo', calcularCostoTotal);

    // ========== ENVÍO DEL FORMULARIO ==========
    
    $('#formFichaTecnica').on('submit', function(e) {
        e.preventDefault();
        
        const tallaInicio = parseInt($('#talla_inicio').val());
        const tallaFin = parseInt($('#talla_fin').val());
        
        if (tallaInicio > tallaFin) {
            Swal.fire('Error', 'La talla inicial debe ser menor o igual a la talla final', 'error');
            return;
        }
        
        let procesosValidos = 0;
        $('.proceso-select').each(function() {
            if ($(this).val()) procesosValidos++;
        });
        
        if (procesosValidos === 0) {
            Swal.fire('Error', 'Debe seleccionar al menos un proceso', 'error');
            return;
        }
        
        let materialesValidos = 0;
        $('.material-select').each(function() {
            if ($(this).val()) materialesValidos++;
        });
        
        if (materialesValidos === 0) {
            Swal.fire('Error', 'Debe seleccionar al menos un material', 'error');
            return;
        }
        
        const formData = new FormData(this);
        
        const genero = $('input[name="genero"]:checked').val();
        let tallasArray = [];
        for (let i = tallaInicio; i <= tallaFin; i++) {
            tallasArray.push({ talla: i, genero: genero });
        }
        
        formData.delete('talla_inicio');
        formData.delete('talla_fin');
        
        tallasArray.forEach((tallaObj, index) => {
            formData.append(`tallas[${index}][talla]`, tallaObj.talla);
            formData.append(`tallas[${index}][genero]`, tallaObj.genero);
        });
        
        $('.material-select').each(function(index) {
            const $row = $(this).closest('tr');
            if ($(this).val()) {
                formData.set(`materiales[${index}][id_material]`, $(this).val());
                formData.set(`materiales[${index}][costo_unitario]`, $row.find('.material-costo').val());
                formData.set(`materiales[${index}][total]`, $row.find('.material-total').val());
            }
        });
        
        Swal.fire({
            title: 'Procesando...',
            text: 'Guardando ficha técnica',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: '../controllers/guardar_ficha_tecnica.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Respuesta del servidor:', response);
                try {
                    const result = typeof response === 'string' ? JSON.parse(response) : response;
                    if (result.success) {
                        Swal.fire('¡Éxito!', 'Ficha técnica guardada correctamente', 'success');
                        $('#nuevaFichaModal').modal('hide');
                        table.ajax.reload();
                        $('#formFichaTecnica')[0].reset();
                        actualizarVistaPreviewTallas();
                    } else {
                        Swal.fire('Error', result.message || 'Error al guardar la ficha técnica', 'error');
                    }
                } catch (e) {
                    console.error('Error al procesar respuesta:', e, response);
                    Swal.fire('Error', 'Error al procesar la respuesta del servidor', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', status, error, xhr.responseText);
                Swal.fire('Error', 'Error al conectar con el servidor', 'error');
            }
        });
    });

    // ========== MODAL VER FICHA ==========
    
    $(document).on('click', '.btn-ver', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Cargando...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        
        $.get(`../controllers/obtener_ficha_tecnica.php?id=${id}`, function(response) {
            try {
                const result = typeof response === 'string' ? JSON.parse(response) : response;
                if (result.success) {
                    const ficha = result.data;
                    let html = `
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center mb-3">
                                    <img src="${ficha.imagen || '../assets/img/no-image.png'}" 
                                         class="img-fluid rounded shadow" style="max-height: 300px;">
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <h4>${ficha.referencia}</h4>
                                        <p><strong>Descripción:</strong> ${ficha.descripcion || 'Sin descripción'}</p>
                                        <p><strong>Categoría:</strong> ${ficha.nombre_categoria}</p>
                                        <p><strong>Color:</strong> ${ficha.color}</p>
                                        <p><strong>Suela:</strong> ${ficha.suela}</p>
                                        <p><strong>Fecha:</strong> ${ficha.fecha_creacion}</p>
                                        <p><strong>Estado:</strong> 
                                            <span class="badge ${ficha.estado == 1 ? 'bg-success' : 'bg-secondary'}">
                                                ${ficha.estado == 1 ? 'Activo' : 'Inactivo'}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="card mb-3">
                                    <div class="card-header bg-light"><h5 class="mb-0">Tallas</h5></div>
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap gap-2">
                    `;
                    
                    if (ficha.tallas && ficha.tallas.length > 0) {
                        ficha.tallas.forEach(t => {
                            html += `<span class="badge bg-primary">${t.talla}</span>`;
                        });
                    } else {
                        html += '<p class="text-muted">No hay tallas</p>';
                    }
                    
                    html += `</div></div></div>
                             <div class="card mb-3">
                                <div class="card-header bg-light"><h5 class="mb-0">Procesos</h5></div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Proceso</th>
                                                <th>Mano de Obra</th>
                                                <th>% Liquidación</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;
                    
                    if (ficha.procesos && ficha.procesos.length > 0) {
                        ficha.procesos.forEach(p => {
                            html += `<tr>
                                <td>${p.nombre_proceso}</td>
                                <td>$${parseFloat(p.mano_obra).toFixed(2)}</td>
                                <td>${p.liquidacion}%</td>
                                <td>$${parseFloat(p.total).toFixed(2)}</td>
                            </tr>`;
                        });
                    }
                    
                    html += `</tbody>
                            <tfoot><tr>
                                <td colspan="3" class="text-end fw-bold">Total:</td>
                                <td class="fw-bold">$${parseFloat(ficha.total_procesos || 0).toFixed(2)}</td>
                            </tr></tfoot>
                        </table></div></div>
                        
                        <div class="card mb-3">
                            <div class="card-header bg-light"><h5 class="mb-0">Materiales</h5></div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Material</th>
                                            <th>Descripción</th>
                                            <th>Cantidad</th>
                                            <th>Costo Unit.</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;
                    
                    if (ficha.materiales && ficha.materiales.length > 0) {
                        ficha.materiales.forEach(m => {
                            html += `<tr>
                                <td>${m.nombre_material}</td>
                                <td>${m.descripcion || '-'}</td>
                                <td>${m.cantidad} ${m.unidad_medida || ''}</td>
                                <td>$${parseFloat(m.costo_unitario).toFixed(2)}</td>
                                <td>$${parseFloat(m.total).toFixed(2)}</td>
                            </tr>`;
                        });
                    }
                    
                    html += `</tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total Materiales:</td>
                                    <td class="fw-bold">$${parseFloat(ficha.total_materiales || 0).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total Procesos:</td>
                                    <td class="fw-bold">$${parseFloat(ficha.total_procesos || 0).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total Costos Fijos:</td>
                                    <td class="fw-bold">$${parseFloat(ficha.total_costos_fijos || 0).toFixed(2)}</td>
                                </tr>
                                <tr class="table-primary">
                                    <td colspan="4" class="text-end fw-bold">Costo Total:</td>
                                    <td class="fw-bold">$${parseFloat(ficha.costo_total || 0).toFixed(2)}</td>
                                </tr>
                            </tfoot>
                        </table></div></div>
                    </div></div>`;
                    
                    $('#detalleFicha').html(html);
                    Swal.close();
                    $('#verFichaModal').modal('show');
                } else {
                    Swal.fire('Error', result.message || 'Error al cargar', 'error');
                }
            } catch (e) {
                console.error('Error:', e, response);
                Swal.fire('Error', 'Error al procesar respuesta', 'error');
            }
        }).fail(function() {
            Swal.fire('Error', 'Error al conectar', 'error');
        });
    });
    
    // Cambiar estado
    $(document).on('click', '.btn-estado', function() {
        const id = $(this).data('id');
        const estadoActual = $(this).data('estado');
        const nuevoEstado = estadoActual == 1 ? 0 : 1;
        const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
        
        Swal.fire({
            title: '¿Está seguro?',
            text: `¿Desea ${accion} esta ficha?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('../controllers/cambiar_estado_ficha.php', {
                    id: id,
                    estado: nuevoEstado
                }, function(response) {
                    try {
                        const result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.success) {
                            Swal.fire('¡Éxito!', `Ficha ${accion}ada`, 'success');
                            table.ajax.reload();
                        } else {
                            Swal.fire('Error', result.message, 'error');
                        }
                    } catch (e) {
                        Swal.fire('Error', 'Error al procesar respuesta', 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Error al conectar', 'error');
                });
            }
        });
    });
    
    // Limpiar formulario al cerrar modal
    $('#nuevaFichaModal').on('hidden.bs.modal', function() {
        $('#formFichaTecnica')[0].reset();
        $('#fichaTabs button:first').tab('show');
        
        $('#procesosBody').html($('#procesosBody tr:first').prop('outerHTML'));
        $('#procesosBody tr').find('select, input').val('');
        $('#procesosBody tr').find('.mano-obra, .total-proceso').val('');
        
        $('#materialesBody').html($('#materialesBody tr:first').prop('outerHTML'));
        $('#materialesBody tr').find('select').val('');
        $('#materialesBody tr').find('input').val('');
        $('#materialesBody tr').find('.material-cantidad').val('1');
        $('#materialesBody tr').find('.material-ancho, .material-alto').val('0');
        
        $('#totalProcesos, #totalProcesosResumen, #totalMateriales, #totalCostosFijos, #costoTotalProducto').text('$0.00');
        
        actualizarVistaPreviewTallas();
    });
    
    // Inicializar vista previa
    actualizarVistaPreviewTallas();
});
</script>