<?php
include_once '../includes/header.php';
include_once '../includes/sidebar.php';
include_once '../config/conexion.php';

// Obtener clientes activos
$stmt_clientes = $pdo->query("SELECT id, razon_social, nit, telefono, ciudad, direccion, tipo_persona FROM tb_clientes WHERE estado = 'Activo' ORDER BY razon_social");
$clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);

// Obtener fichas técnicas activas
$stmt_fichas = $pdo->query("SELECT id, referencia, color, suela, horma, descripcion FROM fichas_tecnicas WHERE estado = 1 ORDER BY referencia");
$fichas_tecnicas = $stmt_fichas->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content p-4">
    <div class="container-fluid">
        <br><br><br>
        <h2 class="fw-bold text-dark mb-4">Ingreso de Pedidos</h2>

        <!-- Pestañas -->
        <ul class="nav nav-tabs mb-4" id="pedidosTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="lista-tab" data-bs-toggle="tab" data-bs-target="#lista" type="button" role="tab">
                    <i class="bi bi-list-ul"></i> Lista de Pedidos
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo" type="button" role="tab">
                    <i class="bi bi-plus-circle"></i> Nuevo Pedido
                </button>
            </li>
        </ul>

        <div class="tab-content" id="pedidosTabsContent">
            <!-- Tab Lista de Pedidos -->
            <div class="tab-pane fade show active" id="lista" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header fw-bold text-white">
                        <i class="bi bi-list-ul"></i> Pedidos Registrados
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="buscarPedido" placeholder="Buscar pedido...">
                            </div>
                            <select class="form-select w-auto" id="filtroEstado">
                                <option value="">Todos los estados</option>
                                <option value="Guardado">Guardado</option>
                                <option value="Espera">Espera</option>
                                <option value="En Producción">En Producción</option>
                                <option value="Remisión">Remisión</option>
                            </select>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="tablaPedidos">
                                <thead class="table-light">
                                    <tr>
                                        <th>Número</th>
                                        <th>Cliente</th>
                                        <th>Fecha Orden</th>
                                        <th>Fecha Entrega</th>
                                        <th>Estado</th>
                                        <th>Items</th>
                                        <th style="width: 200px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT p.*, c.razon_social, 
                                            (SELECT COUNT(*) FROM tb_pedido_items WHERE id_pedido = p.id) as total_items
                                            FROM tb_pedidos p
                                            INNER JOIN tb_clientes c ON p.id_cliente = c.id
                                            ORDER BY p.created_at DESC";
                                    $stmt = $pdo->query($sql);
                                    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    if (count($pedidos) > 0) {
                                        foreach ($pedidos as $pedido) {
                                            $badge_class = match($pedido['estado']) {
                                                'Guardado' => 'bg-secondary',
                                                'Espera' => 'bg-warning text-dark',
                                                'En Producción' => 'bg-info text-dark',
                                                'Remisión' => 'bg-success',
                                                default => 'bg-secondary'
                                            };
                                            
                                            echo "<tr>
                                                <td><strong>{$pedido['numero_pedido']}</strong></td>
                                                <td>{$pedido['razon_social']}</td>
                                                <td>" . date('d/m/Y', strtotime($pedido['fecha_orden'])) . "</td>
                                                <td>" . date('d/m/Y', strtotime($pedido['fecha_entrega'])) . "</td>
                                                <td><span class='badge {$badge_class}'>{$pedido['estado']}</span></td>
                                                <td><span class='badge bg-primary'>{$pedido['total_items']}</span></td>
                                                <td>
                                                    <button class='btn btn-sm btn-outline-primary ver-pedido' data-id='{$pedido['id']}' title='Ver detalles'>
                                                        <i class='bi bi-eye'></i>
                                                    </button>
                                                    <button class='btn btn-sm btn-outline-warning editar-pedido' data-id='{$pedido['id']}' title='Editar'>
                                                        <i class='bi bi-pencil'></i>
                                                    </button>
                                                    <button class='btn btn-sm btn-outline-danger eliminar-pedido' data-id='{$pedido['id']}' title='Eliminar'>
                                                        <i class='bi bi-trash'></i>
                                                    </button>
                                                </td>
                                            </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7' class='text-center text-muted py-4'>No hay pedidos registrados</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Nuevo Pedido -->
            <div class="tab-pane fade" id="nuevo" role="tabpanel">
                <form id="formNuevoPedido">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header fw-bold text-white">
                            Información del Pedido
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Cliente <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_cliente" name="id_cliente" required>
                                        <option value="">Seleccione un cliente</option>
                                        <?php foreach ($clientes as $cliente): ?>
                                            <option value="<?= $cliente['id'] ?>" 
                                                    data-nit="<?= htmlspecialchars($cliente['nit']) ?>"
                                                    data-telefono="<?= htmlspecialchars($cliente['telefono']) ?>"
                                                    data-ciudad="<?= htmlspecialchars($cliente['ciudad']) ?>"
                                                    data-direccion="<?= htmlspecialchars($cliente['direccion']) ?>"
                                                    data-tipo="<?= htmlspecialchars($cliente['tipo_persona']) ?>">
                                                <?= htmlspecialchars($cliente['razon_social']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Número de Pedido <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="numero_pedido" name="numero_pedido" readonly required>
                                    <small class="text-muted">Se generará automáticamente</small>
                                </div>
                            </div>

                            <!-- Información del Cliente Seleccionado -->
                            <div id="infoCliente" class="mt-3 d-none">
                                <hr>
                                <h6 class="fw-bold">Información del Cliente</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <small class="text-muted">NIT/Documento:</small>
                                        <p class="mb-1" id="cliente_nit">-</p>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Teléfono:</small>
                                        <p class="mb-1" id="cliente_telefono">-</p>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Ciudad:</small>
                                        <p class="mb-1" id="cliente_ciudad">-</p>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Tipo:</small>
                                        <p class="mb-1" id="cliente_tipo">-</p>
                                    </div>
                                    <div class="col-md-12">
                                        <small class="text-muted">Dirección:</small>
                                        <p class="mb-0" id="cliente_direccion">-</p>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de Orden <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha_orden" name="fecha_orden" 
                                           value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de Entrega <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha_entrega" name="fecha_entrega" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Observaciones</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Artículos del Pedido -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header fw-bold text-white d-flex justify-content-between align-items-center">
                            <span> Artículos del Pedido</span>
                            <button type="button" class="btn btn-sm btn-light" id="btnAgregarArticulo">
                                <i class="bi bi-plus-lg"></i> Agregar Artículo
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="articulosContainer">
                                <div class="alert alert-info text-center">
                                    <i class="bi bi-info-circle"></i> No hay artículos agregados. 
                                    Haga clic en "Agregar Artículo" para comenzar.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" id="btnCancelar">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                                <button type="button" class="btn btn-warning" id="btnGuardarEspera">
                                    <i class="bi bi-clock"></i> Guardar en Espera
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Guardar Pedido
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Seleccionar Ficha Técnica -->
<div class="modal fade" id="modalSeleccionarFicha" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title">Seleccionar Ficha Técnica</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="buscarFicha" placeholder="Buscar por referencia...">
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Referencia</th>
                                <th>Color</th>
                                <th>Suela</th>
                                <th>Horma</th>
                                <th>Descripción</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fichas_tecnicas as $ficha): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($ficha['referencia']) ?></strong></td>
                                    <td><?= htmlspecialchars($ficha['color']) ?></td>
                                    <td><?= htmlspecialchars($ficha['suela']) ?></td>
                                    <td><?= htmlspecialchars($ficha['horma'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars(substr($ficha['descripcion'] ?? '', 0, 50)) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary seleccionar-ficha" 
                                                data-id="<?= $ficha['id'] ?>">
                                            <i class="bi bi-check-lg"></i> Seleccionar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Pedido -->
<div class="modal fade" id="modalVerPedido" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detalles del Pedido</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detallesPedido">
                <div class="text-center py-5">
                    <div class="spinner-border" role="status"></div>
                    <p class="mt-2">Cargando información...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let articulosCount = 0;
let articulosData = [];

document.addEventListener('DOMContentLoaded', function() {
    // Generar número de pedido automático
    generarNumeroPedido();
    
    // Mostrar información del cliente al seleccionar
    document.getElementById('id_cliente').addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const infoDiv = document.getElementById('infoCliente');
        
        if (this.value) {
            document.getElementById('cliente_nit').textContent = option.dataset.nit || '-';
            document.getElementById('cliente_telefono').textContent = option.dataset.telefono || '-';
            document.getElementById('cliente_ciudad').textContent = option.dataset.ciudad || '-';
            document.getElementById('cliente_direccion').textContent = option.dataset.direccion || '-';
            document.getElementById('cliente_tipo').textContent = option.dataset.tipo || '-';
            infoDiv.classList.remove('d-none');
        } else {
            infoDiv.classList.add('d-none');
        }
    });
    
    // Botón agregar artículo
    document.getElementById('btnAgregarArticulo').addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('modalSeleccionarFicha'));
        modal.show();
    });
    
    // Seleccionar ficha técnica
    document.querySelectorAll('.seleccionar-ficha').forEach(btn => {
        btn.addEventListener('click', function() {
            const fichaId = this.dataset.id;
            cargarFichaTecnica(fichaId);
            bootstrap.Modal.getInstance(document.getElementById('modalSeleccionarFicha')).hide();
        });
    });
    
    // Buscar ficha técnica
    document.getElementById('buscarFicha').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('#modalSeleccionarFicha tbody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    
    // Cancelar
    document.getElementById('btnCancelar').addEventListener('click', function() {
        if (confirm('¿Está seguro de cancelar? Se perderán todos los datos ingresados.')) {
            location.reload();
        }
    });
    
    // Guardar en espera
    document.getElementById('btnGuardarEspera').addEventListener('click', function() {
        guardarPedido('Espera');
    });
    
    // Enviar formulario
    document.getElementById('formNuevoPedido').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarPedido('Guardado');
    });
    
    // Ver pedido
    document.querySelectorAll('.ver-pedido').forEach(btn => {
        btn.addEventListener('click', function() {
            verPedido(this.dataset.id);
        });
    });
    
    // Eliminar pedido
    document.querySelectorAll('.eliminar-pedido').forEach(btn => {
        btn.addEventListener('click', function() {
            eliminarPedido(this.dataset.id);
        });
    });
    
    // Buscar pedido
    document.getElementById('buscarPedido').addEventListener('input', filtrarPedidos);
    document.getElementById('filtroEstado').addEventListener('change', filtrarPedidos);
});

function generarNumeroPedido() {
    const fecha = new Date();
    const year = fecha.getFullYear();
    const month = String(fecha.getMonth() + 1).padStart(2, '0');
    const day = String(fecha.getDate()).padStart(2, '0');
    const random = Math.floor(Math.random() * 10000);
    
    document.getElementById('numero_pedido').value = `PED-${year}${month}${day}-${random}`;
}

function cargarFichaTecnica(fichaId) {
    Swal.fire({
        title: 'Cargando ficha técnica...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    fetch(`../controllers/obtener_ficha_tecnica.php?id=${fichaId}`)
        .then(response => response.json())
        .then(data => {
            Swal.close();
            
            if (data.success) {
                agregarArticulo(data.data);
            } else {
                Swal.fire('Error', data.message || 'No se pudo cargar la ficha técnica', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.close();
            Swal.fire('Error', 'Error al cargar la ficha técnica', 'error');
        });
}

function agregarArticulo(ficha) {
    const container = document.getElementById('articulosContainer');
    const articuloId = `articulo-${articulosCount}`;
    
    // Si es el primer artículo, limpiar el mensaje
    if (container.querySelector('.alert')) {
        container.innerHTML = '';
    }
    
    // Guardar datos del artículo
    articulosData[articulosCount] = {
        id_ficha_original: ficha.id,
        referencia: ficha.referencia,
        color: ficha.color,
        suela: ficha.suela,
        horma: ficha.horma || '',
        descripcion: ficha.descripcion || '',
        tallas: ficha.tallas || [],
        procesos: ficha.procesos || [],
        materiales: ficha.materiales || [],
        costos_indirectos: ficha.costos_indirectos || 0,
        costos_financieros: ficha.costos_financieros || 0,
        costos_distribucion: ficha.costos_distribucion || 0
    };
    
    // Crear HTML del artículo
    const articuloHtml = crearHTMLArticulo(articuloId, articulosCount, ficha);
    container.insertAdjacentHTML('beforeend', articuloHtml);
    
    // Incrementar contador
    articulosCount++;
    
    // Agregar event listeners
    agregarEventListenersArticulo(articuloId);
}

function crearHTMLArticulo(articuloId, index, ficha) {
    const tallasHTML = (ficha.tallas || []).map((t, i) => `
        <div class="col-auto">
            <label class="form-label small">Talla ${t.talla} (${t.genero})</label>
            <input type="number" class="form-control form-control-sm" 
                   data-articulo="${index}" 
                   data-talla-index="${i}"
                   value="${t.cantidad || 0}" min="0" style="width: 80px;">
        </div>
    `).join('');
    
    const procesosHTML = (ficha.procesos || []).map((p, i) => `
        <tr>
            <td>${p.nombre_proceso || 'Proceso ' + (i+1)}</td>
            <td>
                <input type="number" class="form-control form-control-sm" 
                       data-articulo="${index}" 
                       data-proceso-index="${i}"
                       data-field="mano_obra"
                       value="${p.mano_obra || 0}" step="0.01" min="0">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm" 
                       data-articulo="${index}" 
                       data-proceso-index="${i}"
                       data-field="liquidacion"
                       value="${p.liquidacion || 0}" step="0.01" min="0">
            </td>
            <td>$${parseFloat(p.total || 0).toFixed(2)}</td>
        </tr>
    `).join('');
    
    const materialesHTML = (ficha.materiales || []).map((m, i) => `
        <tr>
            <td>${m.nombre_material}</td>
            <td>
                <input type="number" class="form-control form-control-sm" 
                       data-articulo="${index}" 
                       data-material-index="${i}"
                       data-field="cantidad"
                       value="${m.cantidad || 0}" step="0.01" min="0">
            </td>
            <td>${m.unidad_medida}</td>
            <td>$${parseFloat(m.costo_unitario || 0).toFixed(2)}</td>
            <td>$${parseFloat(m.total || 0).toFixed(2)}</td>
        </tr>
    `).join('');
    
    return `
        <div class="card mb-3 articulo-item" id="${articuloId}" data-index="${index}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-box-seam"></i> 
                    <span class="referencia-text">${ficha.referencia}</span>
                </h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarArticulo('${articuloId}', ${index})">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </div>
            <div class="card-body">
                <!-- Datos básicos editables -->
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Referencia</label>
                        <input type="text" class="form-control form-control-sm" 
                               data-articulo="${index}" data-field="referencia"
                               value="${ficha.referencia}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Color</label>
                        <input type="text" class="form-control form-control-sm" 
                               data-articulo="${index}" data-field="color"
                               value="${ficha.color}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Suela</label>
                        <input type="text" class="form-control form-control-sm" 
                               data-articulo="${index}" data-field="suela"
                               value="${ficha.suela}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Horma</label>
                        <input type="text" class="form-control form-control-sm" 
                               data-articulo="${index}" data-field="horma"
                               value="${ficha.horma || ''}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control form-control-sm" rows="2"
                                  data-articulo="${index}" data-field="descripcion">${ficha.descripcion || ''}</textarea>
                    </div>
                </div>
                
                <!-- Tallas -->
                <div class="mb-3">
                    <h6 class="fw-bold">Tallas y Cantidades</h6>
                    <div class="row g-2">
                        ${tallasHTML}
                    </div>
                </div>
                
                <!-- Procesos -->
                <div class="mb-3">
                    <h6 class="fw-bold">Procesos</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Proceso</th>
                                    <th>Mano de Obra</th>
                                    <th>% Liquidación</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${procesosHTML}
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Materiales -->
                <div class="mb-3">
                    <h6 class="fw-bold">Materiales indirectos</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Material</th>
                                    <th>Cantidad</th>
                                    <th>Unidad</th>
                                    <th>Costo Unit.</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${materialesHTML}
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Costos Fijos -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Costos Indirectos</label>
                        <input type="number" class="form-control form-control-sm" 
                               data-articulo="${index}" data-field="costos_indirectos"
                               value="${ficha.costos_indirectos || 0}" step="0.01" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Costos Financieros</label>
                        <input type="number" class="form-control form-control-sm" 
                               data-articulo="${index}" data-field="costos_financieros"
                               value="${ficha.costos_financieros || 0}" step="0.01" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Costos Distribución</label>
                        <input type="number" class="form-control form-control-sm" 
                               data-articulo="${index}" data-field="costos_distribucion"
                               value="${ficha.costos_distribucion || 0}" step="0.01" min="0">
                    </div>
                </div>
            </div>
        </div>
    `;
}

function agregarEventListenersArticulo(articuloId) {
    const articulo = document.getElementById(articuloId);
    if (!articulo) return;
    
    // Actualizar datos al cambiar inputs
    articulo.querySelectorAll('input, textarea').forEach(input => {
        input.addEventListener('change', function() {
            const index = parseInt(this.dataset.articulo);
            const field = this.dataset.field;
            
            if (articulosData[index]) {
                if (this.dataset.tallaIndex !== undefined) {
                    const tallaIndex = parseInt(this.dataset.tallaIndex);
                    articulosData[index].tallas[tallaIndex].cantidad = parseFloat(this.value) || 0;
                } else if (this.dataset.procesoIndex !== undefined) {
                    const procesoIndex = parseInt(this.dataset.procesoIndex);
                    articulosData[index].procesos[procesoIndex][field] = parseFloat(this.value) || 0;
                } else if (this.dataset.materialIndex !== undefined) {
                    const materialIndex = parseInt(this.dataset.materialIndex);
                    articulosData[index].materiales[materialIndex][field] = parseFloat(this.value) || 0;
                } else if (field) {
                    articulosData[index][field] = this.value;
                }
            }
        });
    });
}

function eliminarArticulo(articuloId, index) {
    if (confirm('¿Está seguro de eliminar este artículo?')) {
        const articulo = document.getElementById(articuloId);
        if (articulo) {
            articulo.remove();
            delete articulosData[index];
            
            // Si no quedan artículos, mostrar mensaje
            const container = document.getElementById('articulosContainer');
            if (!container.querySelector('.articulo-item')) {
                container.innerHTML = `
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> No hay artículos agregados. 
                        Haga clic en "Agregar Artículo" para comenzar.
                    </div>
                `;
            }
        }
    }
}

function guardarPedido(estado) {
    // Validar datos básicos
    const id_cliente = document.getElementById('id_cliente').value;
    const numero_pedido = document.getElementById('numero_pedido').value;
    const fecha_orden = document.getElementById('fecha_orden').value;
    const fecha_entrega = document.getElementById('fecha_entrega').value;
    
    if (!id_cliente) {
        Swal.fire('Error', 'Debe seleccionar un cliente', 'error');
        return;
    }
    
    if (!fecha_orden || !fecha_entrega) {
        Swal.fire('Error', 'Debe ingresar las fechas del pedido', 'error');
        return;
    }
    
    // Validar que haya al menos un artículo
    const articulosValidos = Object.values(articulosData).filter(a => a !== undefined);
    if (articulosValidos.length === 0) {
        Swal.fire('Error', 'Debe agregar al menos un artículo al pedido', 'error');
        return;
    }
    
    // Preparar datos
    const datos = {
        id_cliente: id_cliente,
        numero_pedido: numero_pedido,
        fecha_orden: fecha_orden,
        fecha_entrega: fecha_entrega,
        estado: estado,
        observaciones: document.getElementById('observaciones').value,
        articulos: articulosValidos
    };
    
    // Mostrar loading
    Swal.fire({
        title: 'Guardando pedido...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    // Enviar datos
    fetch('../controllers/guardar_pedido.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: 'Pedido guardado correctamente',
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error', data.message || 'Error al guardar el pedido', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.close();
        Swal.fire('Error', 'Error al guardar el pedido', 'error');
    });
}

function verPedido(pedidoId) {
    const modal = new bootstrap.Modal(document.getElementById('modalVerPedido'));
    modal.show();
    
    const detalles = document.getElementById('detallesPedido');
    detalles.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border" role="status"></div>
            <p class="mt-2">Cargando información...</p>
        </div>
    `;
    
    fetch(`../controllers/obtener_pedido.php?id=${pedidoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                detalles.innerHTML = construirHTMLDetallePedido(data.data);
            } else {
                detalles.innerHTML = `
                    <div class="alert alert-danger">
                        ${data.message || 'Error al cargar el pedido'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            detalles.innerHTML = `
                <div class="alert alert-danger">
                    Error al cargar la información del pedido
                </div>
            `;
        });
}

function construirHTMLDetallePedido(pedido) {
    const itemsHTML = (pedido.items || []).map(item => `
        <div class="card mb-3">
            <div class="card-header">
                <strong>Referencia: ${item.referencia_personalizada}</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Color:</strong> ${item.color}</p>
                        <p><strong>Suela:</strong> ${item.suela}</p>
                        <p><strong>Horma:</strong> ${item.horma || '-'}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Descripción:</strong> ${item.descripcion || '-'}</p>
                    </div>
                </div>
                
                ${item.tallas && item.tallas.length > 0 ? `
                <div class="mt-3">
                    <strong>Tallas:</strong>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        ${item.tallas.map(t => `
                            <span class="badge bg-secondary">
                                Talla ${t.talla} (${t.genero}): ${t.cantidad} pares
                            </span>
                        `).join('')}
                    </div>
                </div>
                ` : ''}
            </div>
        </div>
    `).join('');
    
    return `
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Información del Pedido</h5>
                <p><strong>Número:</strong> ${pedido.numero_pedido}</p>
                <p><strong>Cliente:</strong> ${pedido.cliente_nombre}</p>
                <p><strong>Fecha Orden:</strong> ${new Date(pedido.fecha_orden).toLocaleDateString('es-ES')}</p>
                <p><strong>Fecha Entrega:</strong> ${new Date(pedido.fecha_entrega).toLocaleDateString('es-ES')}</p>
                <p><strong>Estado:</strong> <span class="badge bg-primary">${pedido.estado}</span></p>
            </div>
            <div class="col-md-6">
                <h5>Observaciones</h5>
                <p>${pedido.observaciones || 'Sin observaciones'}</p>
            </div>
        </div>
        
        <hr>
        
        <h5>Artículos del Pedido</h5>
        ${itemsHTML}
    `;
}

function eliminarPedido(pedidoId) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../controllers/eliminar_pedido.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: pedidoId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('¡Eliminado!', 'El pedido ha sido eliminado', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'No se pudo eliminar el pedido', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error al eliminar el pedido', 'error');
            });
        }
    });
}

function filtrarPedidos() {
    const busqueda = document.getElementById('buscarPedido').value.toLowerCase();
    const estadoFiltro = document.getElementById('filtroEstado').value;
    const filas = document.querySelectorAll('#tablaPedidos tbody tr');
    
    filas.forEach(fila => {
        const texto = fila.textContent.toLowerCase();
        const estadoBadge = fila.querySelector('.badge');
        const estado = estadoBadge ? estadoBadge.textContent : '';
        
        const coincideBusqueda = texto.includes(busqueda);
        const coincideEstado = !estadoFiltro || estado === estadoFiltro;
        
        fila.style.display = (coincideBusqueda && coincideEstado) ? '' : 'none';
    });
}
</script>

<style>

.table-sm td, .table-sm th {
    padding: 0.3rem;
}

.badge {
    font-size: 0.85rem;
}
</style>