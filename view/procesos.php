<?php
include_once '../includes/header.php';
include_once '../includes/sidebar.php';
include_once '../config/conexion.php';

// Obtener todas las operaciones activas para usarlas en los modales
$stmt_operaciones = $pdo->query("SELECT * FROM tb_operaciones WHERE estado = 'Activo' ORDER BY nombre");
$operaciones = $stmt_operaciones->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="main-content p-4">
    <div class="container-fluid"> <br><br><br>
        <h2 class="fw-bold text-dark mb-4">Gestión de Procesos</h2>

        <ul class="nav nav-tabs mb-4" id="procesosTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="procesos-tab" data-bs-toggle="tab" href="#procesos" role="tab">
                    <i class="bi bi-diagram-3"></i> Procesos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="operaciones-tab" data-bs-toggle="tab" href="#operaciones" role="tab">
                    <i class="bi bi-gear"></i> Operaciones
                </a>
            </li>
        </ul>

        <div class="tab-content" id="procesosTabsContent">
            <div class="tab-pane fade show active" id="procesos" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light fw-bold">
                        <i class="bi bi-diagram-3"></i> Procesos
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Gestiona los procesos de producción.</p>

                        <?php if(isset($_GET['msg']) && isset($_GET['msg_type']) && (!isset($_GET['tab']) || $_GET['tab'] === 'procesos')): ?>
                            <div class="alert alert-<?= htmlspecialchars($_GET['msg_type']) ?> alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($_GET['msg']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="col-md-3">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Buscar proceso..." name="buscar_proceso" id="buscarProceso" value="<?= htmlspecialchars($_GET['buscar_proceso'] ?? '') ?>">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarProceso">
                                <i class="bi bi-plus-circle"></i> Agregar Proceso
                            </button>
                        </div>
                        

                        <div class="shadow-sm rounded bg-white">
                            <table class="table table-hover align-middle mb-0 text-center">
                                <thead class="table-primary">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Costo</th>
                                        <th>Costo Terceros</th>
                                        <th>Tiempo Entrega</th>
                                        <th>Estado</th>
                                        <th style="width: 280px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $busqueda = isset($_GET['buscar_proceso']) ? trim($_GET['buscar_proceso']) : '';
                                    $filtro_estado = isset($_GET['estado']) && in_array($_GET['estado'], ['Activo', 'Inactivo']) ? $_GET['estado'] : '';
                                    
                                    $params = [];
                                    $where = [];
                                    
                                    if (!empty($filtro_estado)) {
                                        $where[] = "p.estado = :estado";
                                        $params[':estado'] = $filtro_estado;
                                    }
                                    
                                    if (!empty($busqueda)) {
                                        $where[] = "p.nombre LIKE :busqueda";
                                        $params[':busqueda'] = "%$busqueda%";
                                    }
                                    
                                    $sql = "SELECT p.*, COUNT(po.operacion_id) as total_operaciones 
                                            FROM tb_procesos p 
                                            LEFT JOIN tb_proceso_operaciones po ON p.id = po.proceso_id ";
                                    
                                    if (!empty($where)) {
                                        $sql .= " WHERE " . implode(' AND ', $where);
                                    }
                                    
                                    $sql .= " GROUP BY p.id";
                                    
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute($params);
                                    
                                    $procesos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    if (count($procesos) > 0) {
                                        foreach ($procesos as $proceso) {
                                            echo "
                                            <tr>
                                                <td>" . htmlspecialchars($proceso['id']) . "</td>
                                                <td>" . htmlspecialchars($proceso['nombre']) . "</td>
                                                <td>$" . number_format($proceso['costo'], 2) . "</td>
                                                <td>$" . number_format($proceso['costo_terceros'], 2) . "</td>
                                                <td>" . htmlspecialchars($proceso['tiempo_max_entrega']) . " horas</td>
                                                <td>
                                                    <span class='badge bg-" . ($proceso['estado'] === 'Activo' ? 'success' : 'secondary') . "'>
                                                        " . htmlspecialchars($proceso['estado']) . "
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class=\"d-flex justify-content-center gap-1\">
                                                        <button class=\"btn btn-sm btn-outline-primary\" style=\"width: 40px;\" data-bs-toggle=\"modal\" data-bs-target=\"#modalVerProceso\" data-id=\"" . htmlspecialchars($proceso['id']) . "\" title=\"Ver detalles\">
                                                            <i class=\"bi bi-eye\"></i>
                                                        </button>
                                                        <button class=\"btn btn-sm btn-outline-warning\" style=\"width: 40px;\" data-bs-toggle=\"modal\" data-bs-target=\"#modalEditarProceso\" data-id=\"" . htmlspecialchars($proceso['id']) . "\" title=\"Editar\">
                                                            <i class=\"bi bi-pencil\"></i>
                                                        </button>
                                                        <button class=\"btn btn-sm btn-outline-" . ($proceso['estado'] === 'Activo' ? 'danger' : 'success') . "\" 
                                                                style=\"width: 110px;\"
                                                                onclick=\"cambiarEstadoProceso('" . htmlspecialchars($proceso['id']) . "', '" . htmlspecialchars($proceso['estado']) . "')\"
                                                                title=\"" . ($proceso['estado'] === 'Activo' ? 'Desactivar' : 'Activar') . " proceso\">
                                                            <i class=\"bi " . ($proceso['estado'] === 'Activo' ? 'bi-x-lg' : 'bi-check-lg') . "\"></i> " . 
                                                            ($proceso['estado'] === 'Activo' ? 'Desactivar' : 'Activar') . "
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            ";
                                        }
                                    } else {
                                        echo '<tr><td colspan="7" class="text-center">No se encontraron procesos</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div> 
                </div> <br><br><br>
            </div>

            <div class="tab-pane fade" id="operaciones" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light fw-bold">
                        <i class="bi bi-gear"></i> Operaciones
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Gestiona las operaciones disponibles para los procesos.</p>

                        <?php if(isset($_GET['msg']) && isset($_GET['msg_type']) && isset($_GET['tab']) && $_GET['tab'] === 'operaciones'): ?>
                            <div class="alert alert-<?= htmlspecialchars($_GET['msg_type']) ?> alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($_GET['msg']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <form method="GET" class="input-group w-auto mb-2">
                                <input type="hidden" name="tab" value="operaciones">
                                <input type="text" name="buscar_operacion" class="form-control" placeholder="Buscar operación..." value="<?= isset($_GET['buscar_operacion']) ? htmlspecialchars($_GET['buscar_operacion']) : '' ?>">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </form>
                            <button class="btn btn-primary d-flex align-items-center mb-2" data-bs-toggle="modal" data-bs-target="#modalAgregarOperacion">
                                <i class="bi bi-plus-lg me-2"></i> Añadir Operación
                            </button>
                        </div>

                        <div class="shadow-sm rounded bg-white">
                            <table class="table table-hover align-middle mb-0 text-center">
                                <thead class="table-primary">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Estado</th>
                                        <th style="width: 200px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $busqueda_op = isset($_GET['buscar_operacion']) ? trim($_GET['buscar_operacion']) : '';
                                    $sql_op = "SELECT * FROM tb_operaciones ORDER BY nombre";
                                    
                                    if (!empty($busqueda_op)) {
                                        $sql_op = "SELECT * FROM tb_operaciones WHERE nombre LIKE :busqueda ORDER BY nombre";
                                        $stmt_op = $pdo->prepare($sql_op);
                                        $stmt_op->execute([':busqueda' => "%$busqueda_op%"]);
                                    } else {
                                        $stmt_op = $pdo->query($sql_op);
                                    }
                                    
                                    $operaciones_lista = $stmt_op->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    if (count($operaciones_lista) > 0) {
                                        foreach ($operaciones_lista as $operacion) {
                                            $estado_badge = $operacion['estado'] === 'Activo' ? 'success' : 'secondary';
                                            $icono_estado = $operacion['estado'] === 'Activo' ? 'bi-x-lg' : 'bi-check-lg';
                                            $btn_class = $operacion['estado'] === 'Activo' ? 'danger' : 'success';
                                            $btn_texto = $operacion['estado'] === 'Activo' ? 'Desactivar' : 'Activar';
                                            
                                            echo "
                                            <tr>
                                                <td>" . htmlspecialchars($operacion['id']) . "</td>
                                                <td>" . htmlspecialchars($operacion['nombre']) . "</td>
                                                <td>
                                                    <span class='badge bg-{$estado_badge}'>
                                                        " . htmlspecialchars($operacion['estado']) . "
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class=\"d-flex justify-content-center gap-1\">
                                                        <button class='btn btn-sm btn-outline-{$btn_class}' 
                                                                onclick='cambiarEstadoOperacion({$operacion['id']}, \"" . htmlspecialchars($operacion['estado']) . "\")'
                                                                title='{$btn_texto} operación'>
                                                            <i class='bi {$icono_estado}'></i> {$btn_texto}
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            ";
                                        }
                                    } else {
                                        echo '<tr><td colspan="4" class="text-center">No se encontraron operaciones</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table> 
                        </div>
                    </div> 
                </div>
            </div> <br><br><br>
        </div>
    </div>
</div>

<!-- Modal Agregar Operación -->
<div class="modal fade" id="modalAgregarOperacion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Agregar Operación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../controllers/guardar_operacion.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombreOperacion" class="form-label">Nombre de la Operación <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombreOperacion" name="nombre" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Agregar Proceso -->
<div class="modal fade" id="modalAgregarProceso" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Agregar Proceso</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAgregarProceso">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombreProceso" class="form-label">Nombre del Proceso <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombreProceso" name="nombre" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Operaciones <span class="text-danger">*</span></label>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="10%">Seleccionar</th>
                                        <th width="30%">Operación</th>
                                        <th width="20%">Costo ($)</th>
                                        <th width="20%">Costo Terceros ($)</th>
                                        <th width="20%">Tiempo (horas)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($operaciones) > 0): ?>
                                        <?php foreach ($operaciones as $op): ?>
                                        <tr>
                                            <td class="text-center">
                                                <input class="form-check-input operacion-check" type="checkbox" 
                                                       name="operaciones[]" value="<?= $op['id'] ?>" 
                                                       id="op_<?= $op['id'] ?>">
                                            </td>
                                            <td>
                                                <label class="form-check-label w-100" for="op_<?= $op['id'] ?>">
                                                    <?= htmlspecialchars($op['nombre']) ?>
                                                </label>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm costo" 
                                                       name="costos[<?= $op['id'] ?>]" 
                                                       step="0.01" min="0" disabled>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm costo-terceros" 
                                                       name="costos_terceros[<?= $op['id'] ?>]" 
                                                       step="0.01" min="0" disabled>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm tiempo" 
                                                       name="tiempos[<?= $op['id'] ?>]" 
                                                       step="0.1" min="0.1" disabled>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">No hay operaciones disponibles</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarProceso">
                        <span class="button-text">Guardar Proceso</span>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Proceso -->
<div class="modal fade" id="modalVerProceso" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detalles del Proceso</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleProceso">
                <div class="text-center my-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Cargando información...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Proceso -->
<div class="modal fade" id="modalEditarProceso" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Editar Proceso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../controllers/actualizar_proceso.php" method="POST" id="formEditarProceso">
                <input type="hidden" name="id" id="editarProcesoId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editarNombreProceso" class="form-label">Nombre del Proceso <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editarNombreProceso" name="nombre" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Operaciones <span class="text-danger">*</span></label>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="10%">Seleccionar</th>
                                        <th width="30%">Operación</th>
                                        <th width="20%">Costo ($)</th>
                                        <th width="20%">Costo Terceros ($)</th>
                                        <th width="20%">Tiempo (horas)</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaEditarOperaciones">
                                    <?php if (count($operaciones) > 0): ?>
                                        <?php foreach ($operaciones as $op): ?>
                                        <tr>
                                            <td class="text-center">
                                                <input class="form-check-input operacion-check-editar" type="checkbox" 
                                                       name="operaciones[]" value="<?= $op['id'] ?>" 
                                                       id="op_editar_<?= $op['id'] ?>">
                                            </td>
                                            <td>
                                                <label class="form-check-label w-100" for="op_editar_<?= $op['id'] ?>">
                                                    <?= htmlspecialchars($op['nombre']) ?>
                                                </label>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm costo-editar" 
                                                       name="costos[<?= $op['id'] ?>]" 
                                                       step="0.01" min="0" disabled>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm costo-terceros-editar" 
                                                       name="costos_terceros[<?= $op['id'] ?>]" 
                                                       step="0.01" min="0" disabled>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm tiempo-editar" 
                                                       name="tiempos[<?= $op['id'] ?>]" 
                                                       step="0.1" min="0.1" disabled>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">No hay operaciones disponibles</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <span class="button-text">Guardar Cambios</span>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Habilitar/Deshabilitar campos cuando se marca un checkbox (Agregar)
document.querySelectorAll('.operacion-check').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const row = this.closest('tr');
        const inputs = row.querySelectorAll('input[type="number"]');
        
        inputs.forEach(input => {
            input.disabled = !this.checked;
            if (!this.checked) {
                input.value = '';
            }
        });
    });
});

// Habilitar/Deshabilitar campos cuando se marca un checkbox (Editar)
document.querySelectorAll('.operacion-check-editar').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const row = this.closest('tr');
        const inputs = row.querySelectorAll('input[type="number"]');
        
        inputs.forEach(input => {
            input.disabled = !this.checked;
            if (!this.checked) {
                input.value = '';
            }
        });
    });
});

// Guardar Proceso
document.getElementById('formAgregarProceso').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const btn = document.getElementById('btnGuardarProceso');
    const btnText = btn.querySelector('.button-text');
    const spinner = btn.querySelector('.spinner-border');
    
    // Validar
    const checkboxes = document.querySelectorAll('.operacion-check:checked');
    if (checkboxes.length === 0) {
        alert('Debe seleccionar al menos una operación');
        return;
    }
    
    // Mostrar loading
    btn.disabled = true;
    btnText.textContent = 'Guardando...';
    spinner.classList.remove('d-none');
    
    // Enviar
    fetch('../controllers/guardar_proceso.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btnText.textContent = 'Guardar Proceso';
        spinner.classList.add('d-none');
        
        if (data.success) {
            alert(data.message);
            window.location.href = 'procesos.php?tab=procesos';
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        btn.disabled = false;
        btnText.textContent = 'Guardar Proceso';
        spinner.classList.add('d-none');
        console.error('Error:', error);
        alert('Error al procesar la solicitud');
    });
});

// Cambiar estado operación
function cambiarEstadoOperacion(id, estado) {
    if (!confirm(`¿Está seguro de ${estado === 'Activo' ? 'desactivar' : 'activar'} esta operación?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('id', id);
    formData.append('estado', estado);
    
    fetch('../controllers/cambiar_estado_operacion.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'procesos.php?tab=operaciones&msg=' + encodeURIComponent(data.message) + '&msg_type=success';
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la solicitud');
    });
}

// Cambiar estado del proceso
function cambiarEstadoProceso(id, estadoActual) {
    const nuevoEstado = estadoActual === 'Activo' ? 'Inactivo' : 'Activo';
    const accion = estadoActual === 'Activo' ? 'desactivar' : 'activar';
    
    if (!confirm(`¿Está seguro de ${accion} este proceso?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('id', id);
    formData.append('estado', nuevoEstado);
    
    fetch('../controllers/cambiar_estado_proceso.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'procesos.php?msg=' + encodeURIComponent(data.message) + '&msg_type=success';
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la solicitud');
    });
}

// Modal ver proceso
document.getElementById('modalVerProceso').addEventListener('show.bs.modal', function(e) {
    const button = e.relatedTarget;
    const procesoId = button.getAttribute('data-id');
    
    // Mostrar loading
    const detalleProceso = document.getElementById('detalleProceso');
    detalleProceso.innerHTML = `
        <div class="text-center my-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Cargando información...</p>
        </div>
    `;
    
    // Obtener detalles del proceso
    fetch(`../controllers/obtener_proceso.php?id=${procesoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success !== false) {
                const proceso = data;
                let operacionesHtml = '';
                
                if (proceso.operaciones && proceso.operaciones.length > 0) {
                    operacionesHtml = `
                        <h5 class="mt-4 mb-3">Operaciones del Proceso</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Operación</th>
                                        <th>Costo ($)</th>
                                        <th>Costo Terceros ($)</th>
                                        <th>Tiempo (horas)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${proceso.operaciones.map((op, index) => `
                                        <tr>
                                            <td>${index + 1}</td>
                                            <td>${op.nombre}</td>
                                            <td>${parseFloat(op.costo || 0).toFixed(2)}</td>
                                            <td>${parseFloat(op.costo_terceros || 0).toFixed(2)}</td>
                                            <td>${parseFloat(op.tiempo_estimado || 0).toFixed(2)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td colspan="2" class="text-end">TOTALES:</td>
                                        <td>${parseFloat(proceso.costo || 0).toFixed(2)}</td>
                                        <td>${parseFloat(proceso.costo_terceros || 0).toFixed(2)}</td>
                                        <td>${parseFloat(proceso.tiempo_max_entrega || 0).toFixed(2)}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    `;
                } else {
                    operacionesHtml = '<div class="alert alert-info">No hay operaciones asignadas a este proceso.</div>';
                }
                
                detalleProceso.innerHTML = `
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>ID:</strong> ${proceso.id}</p>
                                            <p><strong>Nombre:</strong> ${proceso.nombre}</p>
                                            <p><strong>Estado:</strong> <span class="badge bg-${proceso.estado === 'Activo' ? 'success' : 'secondary'}">${proceso.estado}</span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Costo Total:</strong> ${parseFloat(proceso.costo || 0).toFixed(2)}</p>
                                            <p><strong>Costo Terceros Total:</strong> ${parseFloat(proceso.costo_terceros || 0).toFixed(2)}</p>
                                            <p><strong>Tiempo Total:</strong> ${parseFloat(proceso.tiempo_max_entrega || 0).toFixed(2)} horas</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    ${operacionesHtml}
                `;
            } else {
                detalleProceso.innerHTML = `
                    <div class="alert alert-danger">
                        ${data.message || 'Error al cargar los datos del proceso'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            detalleProceso.innerHTML = `
                <div class="alert alert-danger">
                    Error al cargar los datos del proceso. Por favor, intente nuevamente.
                </div>
            `;
        });
});

// Modal editar proceso
document.getElementById('modalEditarProceso').addEventListener('show.bs.modal', function(e) {
    const button = e.relatedTarget;
    const procesoId = button.getAttribute('data-id');
    const form = document.getElementById('formEditarProceso');
    
    console.log('Abriendo modal para editar proceso ID:', procesoId);
    
    // Limpiar el formulario
    form.reset();
    document.getElementById('editarProcesoId').value = procesoId;
    
    // Desmarcar todos los checkboxes y deshabilitar inputs
    document.querySelectorAll('.operacion-check-editar').forEach(checkbox => {
        checkbox.checked = false;
        const row = checkbox.closest('tr');
        const inputs = row.querySelectorAll('input[type="number"]');
        inputs.forEach(input => {
            input.disabled = true;
            input.value = '';
        });
    });
    
    // Obtener datos del proceso
    fetch(`../controllers/obtener_proceso.php?id=${procesoId}`)
        .then(response => response.json())
        .then(data => {
            console.log('Datos recibidos del proceso:', data);
            
            if (data.success !== false) {
                const proceso = data;
                
                // Llenar el formulario
                document.getElementById('editarNombreProceso').value = proceso.nombre;
                
                // Marcar las operaciones seleccionadas
                if (proceso.operaciones && proceso.operaciones.length > 0) {
                    console.log('Procesando operaciones:', proceso.operaciones);
                    
                    proceso.operaciones.forEach(op => {
                        console.log('Procesando operación:', op);
                        
                        // El ID de la operación viene en operacion_id
                        const operacionId = op.operacion_id;
                        const checkbox = document.querySelector(`#op_editar_${operacionId}`);
                        
                        console.log('Buscando checkbox:', `#op_editar_${operacionId}`, 'Encontrado:', checkbox);
                        
                        if (checkbox) {
                            checkbox.checked = true;
                            const row = checkbox.closest('tr');
                            const inputs = row.querySelectorAll('input[type="number"]');
                            inputs.forEach(input => input.disabled = false);
                            
                            // Llenar los valores de la operación específica
                            const costoInput = row.querySelector(`input[name="costos[${operacionId}]"]`);
                            const costoTercerosInput = row.querySelector(`input[name="costos_terceros[${operacionId}]"]`);
                            const tiempoInput = row.querySelector(`input[name="tiempos[${operacionId}]"]`);
                            
                            console.log('Inputs encontrados:', {
                                costo: costoInput,
                                costoTerceros: costoTercerosInput,
                                tiempo: tiempoInput
                            });
                            
                            console.log('Valores a establecer:', {
                                costo: op.costo,
                                costo_terceros: op.costo_terceros,
                                tiempo_estimado: op.tiempo_estimado
                            });
                            
                            if (costoInput) {
                                costoInput.value = parseFloat(op.costo || 0);
                                console.log('Costo establecido:', costoInput.value);
                            }
                            if (costoTercerosInput) {
                                costoTercerosInput.value = parseFloat(op.costo_terceros || 0);
                                console.log('Costo terceros establecido:', costoTercerosInput.value);
                            }
                            if (tiempoInput) {
                                tiempoInput.value = parseFloat(op.tiempo_estimado || 0);
                                console.log('Tiempo establecido:', tiempoInput.value);
                            }
                        } else {
                            console.error('No se encontró el checkbox para la operación:', operacionId);
                        }
                    });
                } else {
                    console.log('No hay operaciones en el proceso');
                }
            } else {
                console.error('Error en respuesta:', data);
                alert(data.message || 'Error al cargar los datos del proceso');
            }
        })
        .catch(error => {
            console.error('Error en fetch:', error);
            alert('Error al cargar los datos del proceso');
        });
});

// Manejar envío del formulario de editar proceso
document.getElementById('formEditarProceso').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    const btnSubmit = form.querySelector('button[type="submit"]');
    const btnText = btnSubmit.querySelector('.button-text');
    const spinner = btnSubmit.querySelector('.spinner-border');
    
    // Validar que se hayan seleccionado operaciones
    const checkboxes = form.querySelectorAll('.operacion-check-editar:checked');
    if (checkboxes.length === 0) {
        alert('Debe seleccionar al menos una operación');
        return;
    }
    
    // Mostrar loading
    btnSubmit.disabled = true;
    btnText.textContent = 'Guardando...';
    spinner.classList.remove('d-none');
    
    // Enviar datos
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btnSubmit.disabled = false;
        btnText.textContent = 'Guardar Cambios';
        spinner.classList.add('d-none');
        
        if (data.success) {
            alert(data.message);
            window.location.href = 'procesos.php';
        } else {
            alert(data.message || 'Error al actualizar el proceso');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btnSubmit.disabled = false;
        btnText.textContent = 'Guardar Cambios';
        spinner.classList.add('d-none');
        alert('Error al procesar la solicitud');
    });
});

// Manejar pestañas
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    
    if (tab === 'operaciones') {
        document.querySelector('#operaciones-tab').click();
    }
    
    // Manejar envío del formulario de agregar operación
    const formAgregarOperacion = document.querySelector('form[action*="guardar_operacion.php"]');
    if (formAgregarOperacion) {
        formAgregarOperacion.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalAgregarOperacion'));
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = 'procesos.php?tab=operaciones&msg=' + encodeURIComponent(data.message) + '&msg_type=success';
                } else {
                    alert(data.message || 'Error al guardar la operación');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        });
    }
});
</script>

<?php include_once '../includes/footer.php'; ?>