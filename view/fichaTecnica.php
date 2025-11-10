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
        <h2 class="fw-bold text-dark mb-4">Gestión de Fichas Técnicas</h2>

        <div class="card shadow-sm border-0">
            <div class="card-header fw-bold">
                <i class="bi bi-file-earmark-text"></i> Fichas Técnicas
            </div>
            <div class="card-body">
                <p class="text-muted">Gestiona las fichas técnicas de los productos.</p>

                <?php if(isset($_GET['msg']) && isset($_GET['msg_type'])): ?>
                    <div class="alert alert-<?= htmlspecialchars($_GET['msg_type']) ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_GET['msg']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Buscar ficha..." id="buscarFicha">
                            <button class="btn btn-outline-primary" type="button" id="btnBuscarFicha">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevaFichaModal">
                        <i class="bi bi-plus-circle"></i> Agregar Ficha
                    </button>
                </div>

                <div class="table-responsive">
                    <table id="tablaFichas" class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th>Referencia</th>
                                <th>Descripción</th>
                                <th>Categoría</th>
                                <th>Color</th>
                                <th>Estado</th>
                                <th style="width: 200px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Los datos se cargarán mediante AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal para nueva ficha técnica -->
        <div class="modal fade" id="nuevaFichaModal" tabindex="-1" aria-labelledby="nuevaFichaModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-secondary text-white">
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
                                    <button class="nav-link" id="materiales-tab" data-bs-toggle="tab" data-bs-target="#materiales" type="button" role="tab">Materiales Indirectos</button>
                                </li>
                            </ul>

                            <div class="tab-content p-3 border border-top-0 rounded-bottom" id="fichaTabsContent">
                        <!-- Pestaña de Datos Básicos -->
                        <div class="tab-pane fade show active" id="datos-basicos" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="referencia" class="form-label">Referencia <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="referencia" name="referencia" required readonly>
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
                                <div class="col-md-6">
                                    <label for="horma" class="form-label">Horma</label>
                                    <input type="text" class="form-control" id="horma" name="horma">
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
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label for="talla_inicio" class="form-label">Talla Inicial</label>
                                        <input type="number" class="form-control" id="talla_inicio" name="talla_inicio" min="20" max="60" step="1" value="36" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="talla_fin" class="form-label">Talla Final</label>
                                        <input type="number" class="form-control" id="talla_fin" name="talla_fin" min="20" max="60" step="1" value="42" required>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="button" id="btnGenerarTallas" class="btn btn-outline-primary">Generar tabla de tallas</button>
                                    </div>
                                </div>
                                <small class="text-muted">Ingrese el rango y haga clic en "Generar tabla de tallas". Luego indique la cantidad de pares por talla.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Tabla de Tallas</label>
                                <div id="tallasTableContainer" class="border rounded p-2">
                                    <!-- Aquí se generará la tabla de tallas y las cantidades -->
                                    <p class="text-muted">Pulse "Generar tabla de tallas" para crear la tabla.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- CORRECCIÓN TABLA DE PROCESOS -->
                        <div class="tab-pane fade" id="procesos" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-secondary">
                                        <tr>
                                            <th width="25%">Proceso</th>
                                            <th width="18%">Mano de Obra</th>
                                            <th width="18%">% Liquidación</th>
                                            <th width="18%">Total</th>
                                            <th width="10%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="procesosBody">
                                        <tr class="proceso-row">
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
                                                    <i class="fas fa-plus"></i> <span class="d-none d-sm-inline">Agregar</span>
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
                                <div class="card-header">
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
                                    <thead class="card-header">
                                        <tr>
                                            <th width="18%">Material</th>
                                            <th width="12%">Unidad</th>
                                            <th width="14%">Costo Unit.</th>
                                            <th width="10%">Cantidad</th>
                                            <th width="10%">Ancho (cm)</th>
                                            <th width="10%">Alto (cm)</th>
                                            <th width="16%">Total</th>
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
                                                                data-unidad="<?= htmlspecialchars($material['unidad_medida']) ?>"
                                                                data-costo="<?= number_format($material['costo_unitario'], 2, '.', '') ?>">
                                                            <?= htmlspecialchars($material['nombre_material']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
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
                                                    <i></i> <span class="d-none d-sm-inline">Agregar</span>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="6" class="text-end fw-bold">Total Materiales:</td>
                                            <td id="totalMateriales" class="fw-bold">$0.00</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="text-end fw-bold">Total Procesos:</td>
                                            <td id="totalProcesosResumen" class="fw-bold">$0.00</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="text-end fw-bold">Total Costos Fijos:</td>
                                            <td id="totalCostosFijos" class="fw-bold">$0.00</td>
                                            <td></td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td colspan="6" class="text-end fw-bold">Costo Total del Producto:</td>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="button-text">Guardar Ficha</span>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ver ficha técnica -->
<div class="modal fade" id="verFichaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
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
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        responsive: true,
        order: [[0, 'asc']],
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
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
                        <div class="d-flex justify-content-center gap-1">
                            <button class="btn btn-sm btn-outline-primary btn-ver" data-id="${row.id}" title="Ver detalles">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-${row.estado == 1 ? 'danger' : 'success'} btn-estado" 
                                    data-id="${row.id}" data-estado="${row.estado}" 
                                    title="${row.estado == 1 ? 'Desactivar' : 'Activar'} ficha">
                                <i class="bi ${row.estado == 1 ? 'bi-x-lg' : 'bi-check-lg'}"></i>
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
    console.log('=== VERIFICANDO MATERIALES INDIRECTOS ===');
    $('.material-select option').each(function() {
        if ($(this).val()) {
            console.log('Material:', $(this).text().trim(), {
                unidad: $(this).data('unidad'),
                costo: $(this).data('costo')
            });
        }
    });

    // Cuando cambie la categoría, cargar materiales asociados y costos por defecto
    $('#id_categoria').on('change', function() {
        const id_categoria = $(this).val();

        if (!id_categoria) {
            // Restablecer la tabla de materiales a la fila por defecto
            $('#materialesBody').html($('#materialesBody tr:first').prop('outerHTML'));
            $('#materialesBody tr').find('select').val('');
            $('#materialesBody tr').find('input').val('');
            $('#materialesBody tr').find('.material-cantidad').val('1');
            calcularTotalMateriales();
            return;
        }

        Swal.fire({title: 'Cargando materiales...', allowOutsideClick: false, didOpen: () => Swal.showLoading()});

        $.getJSON('../controllers/obtener_materiales_por_categoria.php', { id_categoria: id_categoria }, function(resp) {
            Swal.close();
            if (!resp.success) {
                Swal.fire('Error', resp.message || 'No se pudieron cargar los materiales', 'error');
                return;
            }

            const materiales = resp.data.materiales || [];
            const costos = resp.data.costos || {};

            // Poner costos por defecto en los inputs de costos fijos
            $('#costos_indirectos').val(parseFloat(costos.costos_indirectos || 0).toFixed(2));
            $('#costos_financieros').val(parseFloat(costos.costos_financieros || 0).toFixed(2));
            $('#costos_distribucion').val(parseFloat(costos.costos_distribucion || 0).toFixed(2));

            // Construir filas de materiales prellenadas
            let rows = '';
            if (materiales.length === 0) {
                rows = $('#materialesBody tr:first').prop('outerHTML');
            } else {
                materiales.forEach(function(m, idx) {
                    const index = idx; // 0-based
                    const cantidadDefault = (m.cantidad_default !== null && m.cantidad_default !== undefined) ? m.cantidad_default : 1;
                    rows += `
                        <tr>
                            <td>
                                <input type="hidden" name="materiales[${index}][id_material]" value="${m.id_material}">
                                <input type="text" class="form-control form-control-sm" value="${m.nombre_material}" readonly>
                            </td>
                            <td><input type="text" class="form-control form-control-sm material-unidad" readonly name="materiales[${index}][unidad_medida]" value="${m.unidad_medida || ''}"></td>
                            <td><input type="number" class="form-control form-control-sm material-costo" step="0.01" readonly name="materiales[${index}][costo_unitario]" value="${parseFloat(m.costo_unitario || m.costo || 0).toFixed(2)}"></td>
                            <td><input type="number" class="form-control form-control-sm material-cantidad" name="materiales[${index}][cantidad]" step="0.01" min="0.01" value="${cantidadDefault}"></td>
                            <td><input type="number" class="form-control form-control-sm material-ancho" name="materiales[${index}][ancho]" step="0.01" min="0" value="0"></td>
                            <td><input type="number" class="form-control form-control-sm material-alto" name="materiales[${index}][alto]" step="0.01" min="0" value="0"></td>
                            <td><input type="number" class="form-control form-control-sm material-total" step="0.01" readonly name="materiales[${index}][total]"></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger btn-eliminar-material">
                                    <i></i> <span class="d-none d-sm-inline">Eliminar</span>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }

            $('#materialesBody').html(rows);

            // Recalcular totales
            calcularTotalMateriales();
            calcularCostoTotal();
            // Actualizar referencia automática al cambiar la categoría
            generateReferencia();
        }).fail(function() {
            Swal.close();
            Swal.fire('Error', 'Error al conectar con el servidor', 'error');
        });
    });

    // Generar referencia automática a partir de categoría, color, horma y suela
    function slugify(value) {
        return value.toString().normalize('NFKD')
            .replace(/\s+/g, '-') // Replace spaces with -
            .replace(/[^\w\-]+/g, '') // Remove all non-word chars
            .replace(/\-+/g, '-') // Replace multiple - with single -
            .replace(/^-+/, '') // Trim - from start of text
            .replace(/-+$/, '') // Trim - from end of text
            .toUpperCase();
    }

    function generateReferencia() {
        const categoriaText = $('#id_categoria option:selected').text().trim();
        const color = $('#color').val().trim();
        const horma = $('#horma').val() ? $('#horma').val().trim() : '';
        const suela = $('#suela').val().trim();

        const parts = [];
        if (categoriaText) parts.push(slugify(categoriaText));
        if (color) parts.push(slugify(color));
        if (horma) parts.push(slugify(horma));
        if (suela) parts.push(slugify(suela));

        const ref = parts.join('-');
        if (ref) {
            $('#referencia').val(ref);
        }
    }

    // Disparadores para generar referencia
    $('#color, #horma, #suela').on('input change', generateReferencia);

    // ========== FUNCIONES DE TALLAS ==========
    function actualizarVistaPreviewTallas() {
        // Deprecated: now use explicit generator button. This function kept for backward compatibility.
        const inicio = parseInt($('#talla_inicio').val()) || 36;
        const fin = parseInt($('#talla_fin').val()) || 42;

        if (inicio > fin) {
            $('#tallasTableContainer').html('<div class="text-danger">La talla inicial debe ser menor que la final</div>');
            return;
        }

        renderTallasTable(inicio, fin);
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
        // No auto render to avoid accidental overwrites; keep values ready for user to generate
    });

    // Generar tabla al hacer click
    $('#btnGenerarTallas').on('click', function() {
        const inicio = parseInt($('#talla_inicio').val()) || 36;
        const fin = parseInt($('#talla_fin').val()) || 42;
        if (inicio > fin) {
            Swal.fire('Error', 'La talla inicial debe ser menor o igual a la talla final', 'error');
            return;
        }
        renderTallasTable(inicio, fin);
    });

    function renderTallasTable(inicio, fin) {
        let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Talla</th><th>Cantidad (pares)</th></tr></thead><tbody>';
        for (let i = inicio; i <= fin; i++) {
            html += `<tr data-talla="${i}"><td>${i}</td><td><input type="number" min="0" step="1" class="form-control form-control-sm talla-cantidad" data-talla="${i}" value="0"></td></tr>`;
        }
        html += '</tbody></table></div>';
        html += '<div class="text-end"><small class="text-muted me-2">Total pares:</small><span id="totalPares" class="fw-bold">0</span></div>';
        $('#tallasTableContainer').html(html);

        // Update total on input
        $(document).on('input', '.talla-cantidad', function() {
            let total = 0;
            $('.talla-cantidad').each(function() {
                total += parseInt($(this).val()) || 0;
            });
            $('#totalPares').text(total);
        });
    }

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
    // Cuando se selecciona un proceso: setear mano de obra y cargar operaciones asociadas (solo activas)
    $(document).on('change', '.proceso-select', function() {
        const $select = $(this);
        const $option = $select.find('option:selected');
        const costo = parseFloat($option.data('costo')) || 0;
        const $row = $select.closest('tr');

        console.log('==== PROCESO SELECCIONADO ====');
        console.log('Nombre:', $option.text().trim());
        console.log('Costo desde data-costo:', costo);

        $row.find('.mano-obra').val(costo.toFixed(2));
        calcularTotalProceso($row);
        calcularTotalProcesos();

        const procesoId = $option.val();
        // Eliminar cualquier subrow existente para este proceso antes de cargar
        $row.next('.proceso-ops-row').remove();

        if (!procesoId) return;

        // Mostrar loading breve
        const $loadingRow = $(
            `<tr class="proceso-ops-row"><td colspan="5">Cargando operaciones...</td></tr>`
        );
        $row.after($loadingRow);

        // Obtener las operaciones del proceso (controller filtra ops activas)
        $.getJSON(`../controllers/obtener_proceso.php`, { id: procesoId })
        .done(function(resp) {
            // resp may be the proceso object or an error object
            if (!resp || resp.success === false) {
                $loadingRow.remove();
                Swal.fire('Error', (resp && resp.message) ? resp.message : 'No se pudieron cargar las operaciones', 'error');
                return;
            }

            const operaciones = resp.operaciones || [];

            // Construir subtabla de operaciones
            let opsHtml = `
                <tr class="proceso-ops-row">
                    <td colspan="5">
                        <div class="border rounded p-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>Operaciones del proceso</strong>
                                <div>
                                    <select class="form-select form-select-sm d-inline-block me-2 add-op-select" style="width:250px;"></select>
                                    <button type="button" class="btn btn-sm btn-outline-success btn-add-op">Agregar operación</button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered ops-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Operación</th>
                                            <th>Descripción</th>
                                            <th>Color</th>
                                            <th>Costo</th>
                                            <th>Tiempo (h)</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        `;

            operaciones.forEach(function(op, idx) {
                opsHtml += `
                    <tr class="op-row" data-op-id="${op.operacion_id}">
                        <td class="op-index">${idx + 1}</td>
                        <td>
                            <input type="hidden" class="form-control op-id-input" value="${op.operacion_id}">
                            <input type="text" class="form-control form-control-sm op-name" value="${op.nombre}" readonly>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm op-descripcion" placeholder="Descripción...">
                        </td>
                        <td>
                            <input type="color" class="form-control form-control-sm op-color" value="#ffffff">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm op-costo" step="0.01" min="0" value="${parseFloat(op.costo || 0).toFixed(2)}">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm op-tiempo" step="0.01" min="0" value="${parseFloat(op.tiempo_estimado || 0).toFixed(2)}">
                        </td>
                        <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                <button type="button" class="btn btn-sm btn-danger btn-remove-op"><i></i> <span class="d-none d-sm-inline">Eliminar</span></button>
                            </div>
                            <!-- contenedor donde se agregarán filas de materiales para esta operación -->
                            <div class="op-materials-container mt-2"></div>
                        </td>
                    </tr>
                `;
            });

            opsHtml += `</tbody></table></div></div></td></tr>`;

            $loadingRow.replaceWith(opsHtml);

            // Cargar lista de operaciones activas en el select para agregar
            $.getJSON(`../controllers/listar_operaciones_activas.php`)
            .done(function(listResp) {
                if (!listResp || listResp.success === false) return;
                const $selectAdd = $row.next('.proceso-ops-row').find('.add-op-select');
                $selectAdd.empty().append('<option value="">Seleccione...</option>');
                listResp.data.forEach(function(op){
                    // Only include ops not already present in the table
                    if ($row.next('.proceso-ops-row').find(`.op-row[data-op-id="${op.id}"]`).length === 0) {
                        $selectAdd.append(`<option value="${op.id}" data-nombre="${op.nombre}">${op.nombre}</option>`);
                    }
                });
            }).fail(function(){ /* ignore */ });

            // Después de renderizar, normalizar nombres (índices) de inputs
            reindexProcesos();
            // Calcular mano de obra (suma de costos de operaciones) si aplica
            (function(){
                const $opsBlock = $row.next('.proceso-ops-row');
                if ($opsBlock.length) {
                    let sum = 0;
                    $opsBlock.find('.op-costo').each(function(){ sum += parseFloat($(this).val()) || 0; });
                    $row.find('.mano-obra').val(sum.toFixed(2));
                    calcularTotalProcesos();
                }
            })();
        })
        .fail(function() {
            $loadingRow.remove();
            Swal.fire('Error', 'Error al cargar operaciones del proceso', 'error');
        });
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
            <tr class="proceso-row">
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
                            <i></i> <span class="d-none d-sm-inline">Eliminar</span>
                        </button>
                    </td>
            </tr>
        `;
        $('#procesosBody').append(newRow);
        // Reindex names after adding
        reindexProcesos();
    });
    
    // Eliminar fila de proceso (y su bloque de operaciones si existe)
    $(document).on('click', '.btn-eliminar-proceso', function() {
        if ($('#procesosBody tr.proceso-row').length > 1) {
            const $row = $(this).closest('tr.proceso-row');
            // remove following ops block if present
            $row.next('.proceso-ops-row').remove();
            $row.remove();
            calcularTotalProcesos();
            reindexProcesos();
        } else {
            Swal.fire('Atención', 'Debe mantener al menos un proceso', 'warning');
        }
    });

    // Reindexar procesos y operaciones para asegurar nombres consistentes antes de enviar
    function reindexProcesos() {
        $('#procesosBody').find('tr.proceso-row').each(function(pIndex) {
            const $row = $(this);
            // actualizar nombres principales
            $row.find('.proceso-select').attr('name', `procesos[${pIndex}][id_proceso]`);
            $row.find('.mano-obra').attr('name', `procesos[${pIndex}][mano_obra]`);
            $row.find('.liquidacion').attr('name', `procesos[${pIndex}][liquidacion]`);
            $row.find('.total-proceso').attr('name', `procesos[${pIndex}][total]`);

            // si existe bloque de operaciones justo después
            const $opsBlock = $row.next('.proceso-ops-row');
            if ($opsBlock.length) {
                $opsBlock.find('table.ops-table tbody tr.op-row').each(function(opIndex) {
                    const $opRow = $(this);
                    // set display index
                    $opRow.find('.op-index').text(opIndex + 1);
                    const opId = $opRow.data('op-id') || $opRow.find('.op-id-input').val();
                    // establecer nombres para inputs de operación
                    // id_operacion (hidden)
                    if ($opRow.find('.op-id-input').length) {
                        $opRow.find('.op-id-input').attr('name', `procesos[${pIndex}][operaciones][${opIndex}][id_operacion]`).val(opId);
                    } else {
                        // create hidden input if missing
                        $opRow.prepend(`<input type="hidden" name="procesos[${pIndex}][operaciones][${opIndex}][id_operacion]" class="op-id-input" value="${opId}">`);
                    }
                    $opRow.find('.op-descripcion').attr('name', `procesos[${pIndex}][operaciones][${opIndex}][descripcion]`);
                    $opRow.find('.op-color').attr('name', `procesos[${pIndex}][operaciones][${opIndex}][color]`);
                    $opRow.find('.op-costo').attr('name', `procesos[${pIndex}][operaciones][${opIndex}][costo]`);
                    $opRow.find('.op-tiempo').attr('name', `procesos[${pIndex}][operaciones][${opIndex}][tiempo_estimado]`);
                    // Reindexar materiales asociados a esta operación (si los hay)
                    $opRow.find('.op-materials-container').find('.op-material-row').each(function(matIndex) {
                        const $m = $(this);
                        // hidden inputs inside the material row
                        $m.find('.opmat-id').attr('name', `procesos[${pIndex}][operaciones][${opIndex}][materiales][${matIndex}][id_material]`);
                        $m.find('.opmat-cantidad').attr('name', `procesos[${pIndex}][operaciones][${opIndex}][materiales][${matIndex}][cantidad]`);
                        $m.find('.opmat-costo').attr('name', `procesos[${pIndex}][operaciones][${opIndex}][materiales][${matIndex}][costo_unitario]`);
                        $m.find('.opmat-total').attr('name', `procesos[${pIndex}][operaciones][${opIndex}][materiales][${matIndex}][total]`);
                    });
                });
            }
        });
    }


    // actualizar costo y total en modal
    $('#attach_material_select').on('change', function() {
        const costo = parseFloat($(this).find('option:selected').data('costo')) || 0;
        $('#attach_material_costo').val(costo.toFixed(2));
        const cantidad = parseFloat($('#attach_material_cantidad').val()) || 0;
        $('#attach_material_total').text((costo * cantidad).toFixed(2));
    });

    $('#attach_material_cantidad').on('input', function() {
        const cantidad = parseFloat($(this).val()) || 0;
        const costo = parseFloat($('#attach_material_costo').val()) || 0;
        $('#attach_material_total').text((cantidad * costo).toFixed(2));
    });

    // Agregar material seleccionado a la operación actual
    $('#btnAgregarMaterialOp').on('click', function() {
        if (!currentOpRow) return;
        const matId = $('#attach_material_select').val();
        const matText = $('#attach_material_select option:selected').text();
        const unidad = $('#attach_material_select option:selected').data('unidad') || '';
        const cantidad = parseFloat($('#attach_material_cantidad').val()) || 0;
        const costo = parseFloat($('#attach_material_costo').val()) || 0;
        const total = parseFloat((cantidad * costo).toFixed(2)) || 0;

        if (!matId) { Swal.fire('Atención', 'Seleccione un material', 'warning'); return; }

        // Construir elemento visual y inputs ocultos
        const html = `
            <div class="op-material-row alert alert-light p-2 d-flex justify-content-between align-items-center">
                <div><strong>${matText}</strong> <small class="text-muted">${unidad}</small></div>
                <div>Cantidad: <span class="badge bg-secondary">${cantidad}</span></div>
                <div>Costo: $${costo.toFixed(2)} — Total: $${total.toFixed(2)}</div>
                <div><button type="button" class="btn btn-sm btn-danger btn-remove-op-material">Eliminar</button></div>
                <input type="hidden" class="opmat-id" value="${matId}">
                <input type="hidden" class="opmat-cantidad" value="${cantidad}">
                <input type="hidden" class="opmat-costo" value="${costo}">
                <input type="hidden" class="opmat-total" value="${total}">
            </div>
        `;

        currentOpRow.find('.op-materials-container').append(html);
        reindexProcesos();
        $('#attachMaterialModal').modal('hide');
    });

    // Remover material adjuntado a operación
    $(document).on('click', '.btn-remove-op-material', function() {
        $(this).closest('.op-material-row').remove();
        reindexProcesos();
    });

    // Agregar operación desde el select (delegado)
    $(document).on('click', '.btn-add-op', function() {
        const $opsRow = $(this).closest('.proceso-ops-row');
        const $select = $opsRow.find('.add-op-select');
        const val = $select.val();
        const nombre = $select.find('option:selected').data('nombre') || '';
        if (!val) {
            Swal.fire('Atención', 'Seleccione una operación para agregar', 'warning');
            return;
        }

        // evitar duplicados
        if ($opsRow.find(`.op-row[data-op-id="${val}"]`).length) {
            Swal.fire('Atención', 'La operación ya está agregada', 'warning');
            return;
        }

        const newOp = `
            <tr class="op-row" data-op-id="${val}">
                <td class="op-index">0</td>
                <td>
                    <input type="hidden" class="form-control op-id-input" value="${val}">
                    <input type="text" class="form-control form-control-sm op-name" value="${nombre}" readonly>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm op-descripcion" placeholder="Descripción...">
                </td>
                <td>
                    <input type="color" class="form-control form-control-sm op-color" value="#ffffff">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm op-costo" step="0.01" min="0" value="0">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm op-tiempo" step="0.01" min="0" value="0">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-remove-op" title="Eliminar operación" aria-label="Eliminar operación">
                        <i class="fas fa-trash"></i> <span class="ms-1 d-none d-sm-inline">Eliminar</span>
                    </button>
                </td>
            </tr>
        `;

        $opsRow.find('table.ops-table tbody').append(newOp);
    // recompute mano-obra for the parent processo row
    const $procRow = $opsRow.prev('.proceso-row');
    let sumNew = 0;
    $opsRow.find('.op-costo').each(function(){ sumNew += parseFloat($(this).val()) || 0; });
    if ($procRow.length) $procRow.find('.mano-obra').val(sumNew.toFixed(2));
        // quitar la opción agregada del select para evitar re-agregar
        $select.find(`option[value="${val}"]`).remove();

        reindexProcesos();
    });

    // Remover operación
    $(document).on('click', '.btn-remove-op', function() {
        const $opRow = $(this).closest('tr.op-row');
        const $opsBlock = $opRow.closest('.proceso-ops-row');
        // si existe select de agregar, reinsertar la opción para poder agregarla de nuevo
        const opId = $opRow.data('op-id') || $opRow.find('.op-id-input').val();
        const opName = $opRow.find('.op-name').val() || $opRow.find('.op-name').text() || '';
        if ($opsBlock.length) {
            const $select = $opsBlock.find('.add-op-select');
            if ($select.length) {
                $select.append(`<option value="${opId}" data-nombre="${opName}">${opName}</option>`);
            }
        }
        $opRow.remove();
        reindexProcesos();
    });

    // Si cambia el costo de una operación, recalcular la mano de obra del proceso y totales
    $(document).on('input', '.op-costo', function() {
        const $opRow = $(this).closest('tr.op-row');
        const $opsBlock = $opRow.closest('tbody').closest('table').closest('.proceso-ops-row');
        // encontrar la fila de proceso asociada (anterior)
        const $procRow = $opsBlock.prev('.proceso-row');
        if ($procRow.length) {
            let sum = 0;
            $opsBlock.find('.op-costo').each(function(){ sum += parseFloat($(this).val()) || 0; });
            $procRow.find('.mano-obra').val(sum.toFixed(2));
            calcularTotalProcesos();
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
        
    const unidad = $option.data('unidad') || '';
        
        const costo = parseFloat($option.data('costo')) || 0;
        
    console.log('==== MATERIAL SELECCIONADO ====');
    console.log('Nombre:', $option.text().trim());
    console.log('Unidad:', unidad);
    console.log('Costo:', costo);
        
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
                                                    data-unidad="<?= htmlspecialchars($material['unidad_medida']) ?>"
                                                    data-costo="<?= number_format($material['costo_unitario'], 2, '.', '') ?>">
                                                <?= htmlspecialchars($material['nombre_material']) ?>
                                            </option>
                                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="text" class="form-control form-control-sm material-unidad" readonly></td>
                <td><input type="number" class="form-control form-control-sm material-costo" step="0.01" readonly></td>
                <td><input type="number" class="form-control form-control-sm material-cantidad" name="materiales[${index}][cantidad]" step="0.01" min="0.01" value="1"></td>
                <td><input type="number" class="form-control form-control-sm material-ancho" name="materiales[${index}][ancho]" step="0.01" min="0" value="0"></td>
                <td><input type="number" class="form-control form-control-sm material-alto" name="materiales[${index}][alto]" step="0.01" min="0" value="0"></td>
                <td><input type="number" class="form-control form-control-sm material-total" step="0.01" readonly></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-eliminar-material" title="Eliminar material" aria-label="Eliminar material">
                        <i></i> <span class="ms-1">Eliminar</span>
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
        
        // Contar filas de materiales que tengan un id de material (select o input hidden)
        let materialesValidos = 0;
        $('#materialesBody tr').each(function() {
            const $select = $(this).find('select[name^="materiales"][name$="[id_material]"]');
            const $hidden = $(this).find('input[name^="materiales"][name$="[id_material]"]');
            if ($select.length) {
                if ($select.val()) materialesValidos++;
            } else if ($hidden.length) {
                if ($hidden.val()) materialesValidos++;
            }
        });

        if (materialesValidos === 0) {
            Swal.fire('Error', 'Debe seleccionar al menos un material', 'error');
            return;
        }
        
        const formData = new FormData(this);
        
        const genero = $('input[name="genero"]:checked').val();
        // Serializar tallas con cantidades (si la tabla de tallas se generó)
        formData.delete('talla_inicio');
        formData.delete('talla_fin');

        const tallaInputs = $('#tallasTableContainer').find('.talla-cantidad');
        if (tallaInputs.length) {
            tallaInputs.each(function(index) {
                const talla = $(this).data('talla');
                const cantidad = $(this).val() || 0;
                formData.append(`tallas[${index}][talla]`, talla);
                formData.append(`tallas[${index}][genero]`, genero);
                formData.append(`tallas[${index}][cantidad]`, cantidad);
            });
        } else {
            // Fallback: if no generated table, still send the range as individual tallas with cantidad 0
            let tallasArray = [];
            for (let i = tallaInicio; i <= tallaFin; i++) {
                tallasArray.push({ talla: i, genero: genero });
            }
            tallasArray.forEach((tallaObj, index) => {
                formData.append(`tallas[${index}][talla]`, tallaObj.talla);
                formData.append(`tallas[${index}][genero]`, tallaObj.genero);
                formData.append(`tallas[${index}][cantidad]`, 0);
            });
        }
        
        // Asegurar que el FormData incluya id, costo_unitario y total para cada fila de material
        $('#materialesBody tr').each(function(index) {
            const $row = $(this);
            const $select = $row.find('select[name^="materiales"][name$="[id_material]"]');
            const $hidden = $row.find('input[name^="materiales"][name$="[id_material]"]');
            let idVal = null;
            if ($select.length) idVal = $select.val();
            else if ($hidden.length) idVal = $hidden.val();

            if (idVal) {
                formData.set(`materiales[${index}][id_material]`, idVal);
                // Tomar costo y total desde los inputs (si existen)
                const costo = $row.find('.material-costo').val() || '0';
                const total = $row.find('.material-total').val() || '0';
                formData.set(`materiales[${index}][costo_unitario]`, costo);
                formData.set(`materiales[${index}][total]`, total);
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

    // Helper para escapar texto en HTML
    function esc(s) {
        if (s === null || s === undefined) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // Construir HTML para detalle de ficha (uso de concatenación para evitar template literals complejos)
    function buildFichaHTML(ficha) {
        var html = '';
        html += '<div class="row">';
        html += '<div class="col-md-4">';
        html += '<div class="text-center mb-3">';
        html += '<img src="' + esc(ficha.imagen_url || '../assets/img/no-image.png') + '" class="img-fluid rounded shadow" style="max-height: 300px;">';
        html += '</div>';
        html += '<div class="card"><div class="card-body">';
        html += '<h4>' + esc(ficha.referencia) + '</h4>';
        html += '<p><strong>Descripción:</strong> ' + esc(ficha.descripcion || 'Sin descripción') + '</p>';
        html += '<p><strong>Categoría:</strong> ' + esc(ficha.nombre_categoria) + '</p>';
        html += '<p><strong>Color:</strong> ' + esc(ficha.color) + '</p>';
        html += '<p><strong>Suela:</strong> ' + esc(ficha.suela) + '</p>';
        html += '<p><strong>Fecha:</strong> ' + esc(ficha.fecha_creacion) + '</p>';
        html += '<p><strong>Estado:</strong> <span class="badge ' + (ficha.estado == 1 ? 'bg-success' : 'bg-secondary') + '">' + (ficha.estado == 1 ? 'Activo' : 'Inactivo') + '</span></p>';
        html += '</div></div></div>'; // end left col

        html += '<div class="col-md-8">';
        // Tallas table
        html += '<div class="card mb-3"><div class="card-header"><h5 class="mb-0">Tallas</h5></div><div class="card-body">';
        html += '<div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th>Talla</th><th>Género</th><th>Cantidad (pares)</th></tr></thead><tbody>';
        var totalPares = 0;
        if (ficha.tallas && ficha.tallas.length > 0) {
            for (var ti = 0; ti < ficha.tallas.length; ti++) {
                var t = ficha.tallas[ti];
                var cantidad = (t.cantidad !== undefined && t.cantidad !== null) ? parseInt(t.cantidad) : 0;
                totalPares += cantidad;
                html += '<tr><td>' + esc(t.talla) + '</td><td>' + esc(t.genero || '') + '</td><td>' + cantidad + '</td></tr>';
            }
        } else {
            html += '<tr><td colspan="3" class="text-muted">No hay tallas registradas</td></tr>';
        }
        html += '</tbody><tfoot><tr><td colspan="2" class="text-end fw-bold">Total pares:</td><td class="fw-bold">' + totalPares + '</td></tr></tfoot></table></div></div></div>';

        // Procesos y operaciones
        html += '<div class="card mb-3"><div class="card-header"><h5 class="mb-0">Procesos y Operaciones</h5></div><div class="card-body"><div class="table-responsive">';
        html += '<table class="table table-sm table-bordered"><thead><tr><th>Proceso</th><th>Mano de Obra</th><th>% Liquidación</th><th>Total</th></tr></thead><tbody>';
        if (ficha.procesos && ficha.procesos.length > 0) {
            for (var pi = 0; pi < ficha.procesos.length; pi++) {
                var p = ficha.procesos[pi];
                html += '<tr><td>' + esc(p.nombre_proceso) + '</td><td>$' + (parseFloat(p.mano_obra || 0).toFixed(2)) + '</td><td>' + (p.liquidacion || 0) + '%</td><td>$' + (parseFloat(p.total || 0).toFixed(2)) + '</td></tr>';
                if (p.operaciones && p.operaciones.length > 0) {
                    html += '<tr class="table-light"><td colspan="4">';
                    html += '<div class="fw-bold mb-2">Operaciones del proceso:</div>';
                    html += '<div class="table-responsive"><table class="table table-sm table-bordered mb-0"><thead><tr><th>#</th><th>Operación</th><th>Descripción</th><th>Color</th><th>Costo</th><th>Tiempo (h)</th></tr></thead><tbody>';
                    for (var oi = 0; oi < p.operaciones.length; oi++) {
                        var op = p.operaciones[oi];
                        html += '<tr>';
                        html += '<td>' + (oi + 1) + '</td>';
                        html += '<td>' + esc(op.nombre_operacion || '') + '</td>';
                        html += '<td>' + esc(op.descripcion || '') + '</td>';
                        html += '<td><div style="width:24px;height:16px;background:' + esc(op.color || '#ffffff') + ';border:1px solid #ddd;"></div></td>';
                        html += '<td>$' + (parseFloat(op.costo || 0).toFixed(2)) + '</td>';
                        html += '<td>' + (parseFloat(op.tiempo_estimado || 0).toFixed(2)) + '</td>';
                        '</tr>';
                    }
                    html += '</tbody></table></div></td></tr>';
                }
            }
        } else {
            html += '<tr><td colspan="4" class="text-muted">No hay procesos registrados</td></tr>';
        }
        html += '</tbody><tfoot><tr><td colspan="3" class="text-end fw-bold">Total:</td><td class="fw-bold">$' + (parseFloat(ficha.total_procesos || 0).toFixed(2)) + '</td></tr></tfoot></table></div></div></div>';

        // Materiales indirectos
        html += '<div class="card mb-3"><div class="card-header"><h5 class="mb-0">Materiales Indirectos</h5></div><div class="card-body">';
        html += '<table class="table table-sm"><thead><tr><th>Material</th><th>Cantidad</th><th>Costo Unit.</th><th>Total</th></tr></thead><tbody>';
        if (ficha.materiales && ficha.materiales.length > 0) {
            for (var mi2 = 0; mi2 < ficha.materiales.length; mi2++) {
                var mat = ficha.materiales[mi2];
                html += '<tr><td>' + esc(mat.nombre_material) + '</td><td>' + (parseFloat(mat.cantidad || 0)) + ' ' + esc(mat.unidad_medida || '') + '</td><td>$' + (parseFloat(mat.costo_unitario || 0).toFixed(2)) + '</td><td>$' + (parseFloat(mat.total || 0).toFixed(2)) + '</td></tr>';
            }
        } else {
            html += '<tr><td colspan="4" class="text-muted">No hay materiales indirectos</td></tr>';
        }
        html += '</tbody><tfoot><tr><td colspan="3" class="text-end fw-bold">Total Materiales:</td><td class="fw-bold">$' + (parseFloat(ficha.total_materiales || 0).toFixed(2)) + '</td></tr><tr><td colspan="3" class="text-end fw-bold">Total Procesos:</td><td class="fw-bold">$' + (parseFloat(ficha.total_procesos || 0).toFixed(2)) + '</td></tr><tr><td colspan="3" class="text-end fw-bold">Total Costos Fijos:</td><td class="fw-bold">$' + (parseFloat(ficha.total_costos_fijos || 0).toFixed(2)) + '</td></tr><tr class="table-primary"><td colspan="3" class="text-end fw-bold">Costo Total:</td><td class="fw-bold">$' + (parseFloat(ficha.costo_total || 0).toFixed(2)) + '</td></tr></tfoot></table></div></div>';

        html += '</div></div>'; // end right col and row
        return html;
    }
    
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
                        $('#detalleFicha').html(buildFichaHTML(ficha));
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
            text: '¿Desea ' + accion + ' esta ficha?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, ' + accion,
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
                            Swal.fire('¡Éxito!', 'Ficha ' + accion + 'ada', 'success');
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