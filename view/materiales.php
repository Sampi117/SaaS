<?php
include_once '../includes/header.php';
include_once '../includes/sidebar.php';
include_once '../config/conexion.php';

// Obtener lista de proveedores para el select
$sql_proveedores = "SELECT id, nombre FROM tb_proveedores WHERE estado = 'Activo' ORDER BY nombre";
$proveedores = $pdo->query($sql_proveedores)->fetchAll(PDO::FETCH_ASSOC);

// Obtener operaciones disponibles
$sql_operaciones = "SELECT * FROM tb_operaciones WHERE estado = 'Activo' ORDER BY nombre";
$operaciones = $pdo->query($sql_operaciones)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content p-4">
    <div class="container-fluid"> <br><br><br>
        <!-- Título principal -->
        <h2 class="fw-bold text-dark mb-4">Gestión de Materiales</h2>

        <ul class="nav nav-tabs mb-4" id="articulosTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="materia-tab" data-bs-toggle="tab" href="#materia" role="tab">
                    <i class="bi bi-box-seam"></i> Materiales Directos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="materiales-tab" data-bs-toggle="tab" href="#materiales" role="tab">
                    <i class="bi bi-tools"></i> Materiales Indirectos
                </a>
            </li>
        </ul>

        <div class="tab-content" id="articulosTabsContent">

            <!-- === TAB MATERIALES DIRECTOS === -->
            <div class="tab-pane fade show active" id="materia" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light fw-bold">
                        <i class="bi bi-box-seam"></i> Materiales Directos
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Gestiona los materiales directos utilizados en la producción.</p>

                        <!-- Mensajes -->
                        <?php if(isset($_GET['msg']) && isset($_GET['msg_type'])): ?>
                            <div class="alert alert-<?= $_GET['msg_type'] ?> alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($_GET['msg']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Buscador y Añadir Material -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <form method="GET" class="input-group w-auto mb-2">
                                <input type="hidden" name="tab" value="directos">
                                <input type="text" name="buscar_directo" class="form-control" placeholder="Buscar material directo..." value="<?= isset($_GET['buscar_directo']) ? htmlspecialchars($_GET['buscar_directo']) : '' ?>">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </form>
                            <button class="btn btn-primary d-flex align-items-center mb-2" data-bs-toggle="modal" data-bs-target="#modalAgregarMaterialDirecto">
                                <i class="bi bi-plus-lg me-2"></i> Añadir Material
                            </button>
                        </div>

                        <!-- Tabla de Materiales Directos -->
                        <div class="shadow-sm rounded bg-white">
                            <table class="table table-hover align-middle mb-0 text-center">
                                <thead class="table-primary">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Referencia</th>
                                        <th>Unidad</th>
                                        <th>Talla</th>
                                        <th>Proveedor</th>
                                        <th>Costo</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $busqueda = isset($_GET['buscar_directo']) ? trim($_GET['buscar_directo']) : '';
                                    $sql = "SELECT md.*, p.nombre as nombre_proveedor 
                                            FROM tb_materiales_directos md 
                                            LEFT JOIN tb_proveedores p ON md.proveedor_id = p.id 
                                            WHERE (md.nombre LIKE :buscar OR md.referencia LIKE :buscar OR p.nombre LIKE :buscar)
                                            ORDER BY md.id DESC";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute([':buscar' => "%$busqueda%"]);
                                    $materiales_directos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    if (count($materiales_directos) > 0) {
                                        foreach ($materiales_directos as $mat) {
                                            $tallas = json_decode($mat['tallas_disponibles'], true);
                                            $tallas_str = is_array($tallas) ? implode(', ', $tallas) : 'N/A';
                                            
                                            echo "<tr>";
                                            echo "<td>" . $mat['id'] . "</td>";
                                            echo "<td>" . htmlspecialchars($mat['nombre']) . "<br><small class='text-muted'>" . 
                                                 htmlspecialchars(substr($mat['descripcion'], 0, 50)) . "...</small></td>";
                                            echo "<td>" . htmlspecialchars($mat['referencia']) . "</td>";
                                            echo "<td>" . htmlspecialchars($mat['unidad_medida']) . "</td>";
                                            echo "<td>" . htmlspecialchars($mat['genero']) . "<br><small>" . $tallas_str . "</small></td>";
                                            echo "<td>" . htmlspecialchars($mat['nombre_proveedor']) . "</td>";
                                            echo "<td>$" . number_format($mat['costo'], 2) . "</td>";
                                            echo "<td><span class='badge bg-" . ($mat['estado'] === 'Activo' ? 'success' : 'secondary') . "'>" . $mat['estado'] . "</span></td>";
                                            echo "<td class='text-nowrap'>";
                                            
                                            // Botón para ver detalles
                                            echo "<button class='btn btn-sm btn-outline-primary ver-material-directo me-1' 
                                                    data-id='" . $mat['id'] . "' 
                                                    data-nombre='" . htmlspecialchars($mat['nombre']) . "' 
                                                    data-descripcion='" . htmlspecialchars($mat['descripcion']) . "' 
                                                    data-referencia='" . htmlspecialchars($mat['referencia']) . "' 
                                                    data-unidad='" . htmlspecialchars($mat['unidad_medida']) . "' 
                                                    data-genero='" . htmlspecialchars($mat['genero']) . "' 
                                                    data-proveedor='" . htmlspecialchars($mat['nombre_proveedor']) . "' 
                                                    data-costo='" . $mat['costo'] . "' 
                                                    data-estado='" . $mat['estado'] . "' 
                                                    data-tallas='" . $tallas_str . "' 
                                                    title='Ver Detalles'>";
                                            echo "<i class='bi bi-eye'></i></button>";
                                            
                                            // Botón para activar/desactivar con estilo de categoría
                                            echo "<button class='btn btn-sm btn-outline-primary editar-estado-directo' 
                                                    data-id='" . $mat['id'] . "' 
                                                    data-estado='" . $mat['estado'] . "' 
                                                    title='" . ($mat['estado'] === 'Activo' ? 'Desactivar' : 'Activar') . "'>";
                                            echo "<i class='bi " . ($mat['estado'] === 'Activo' ? 'bi-pause' : 'bi-play') . " me-1'></i>" . ($mat['estado'] === 'Activo' ? 'Inactivar' : 'Activar') . "</button>";
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo '<tr><td colspan="8" class="text-center">No se encontraron materiales directos</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- === TAB MATERIALES INDIRECTOS === -->
            <div class="tab-pane fade" id="materiales" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light fw-bold">
                        <i class="bi bi-tools"></i> Materiales Indirectos
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Gestiona los materiales indirectos del proceso de producción.</p>

                        <!-- MENSAJES -->
                        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'primary'): ?>
                            <div class="alert alert-primary alert-dismissible fade show" role="alert">
                                Material agregado correctamente.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php elseif(isset($_GET['msg']) && $_GET['msg'] == 'error'): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                Error: <?= htmlspecialchars($_GET['error']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Buscador y Añadir Material -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <form method="GET" class="input-group w-auto mb-2">
                                <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre" value="<?= isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '' ?>">
                                <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
                            </form>
                            <button class="btn btn-primary d-flex align-items-center mb-2" data-bs-toggle="modal" data-bs-target="#modalAgregarMaterial">
                                <i class="bi bi-plus-lg me-2"></i> Añadir Material
                            </button>
                        </div>

                        <!-- Tabla de Materiales -->
                        <div class="shadow-sm rounded bg-white">
                            <table class="table table-hover align-middle mb-0 text-center">
                                <thead class="table-primary">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Unidad de Medida</th>
                                        <th>Costo (COP)</th>
                                        <th>Cantidad</th>
                                        <th>Fecha Registro</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    include("../config/conexion.php");
                                    $busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
                                    $sql = "SELECT * FROM tb_materiales_indirectos WHERE nombre LIKE :buscar ORDER BY id DESC";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute(['buscar' => "%$busqueda%"]);
                                    $materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    if (count($materiales) > 0) {
                                        foreach ($materiales as $m) {
                                            echo "<tr>";
                                            $estadoClass = $m['estado'] === 'Activo' ? 'success' : 'secondary';
                                            
                                            echo "<td>" . $m['id'] . "</td>";
                                            echo "<td>" . htmlspecialchars($m['nombre']) . "</td>";
                                            echo "<td>" . htmlspecialchars($m['unidad_medida']) . "</td>";
                                            echo "<td>$" . number_format($m['costo'], 2, ',', '.') . "</td>";
                                            echo "<td>" . $m['cantidad'] . "</td>";
                                            echo "<td>" . $m['fecha_creacion'] . "</td>";
                                            echo "<td><span class='badge bg-{$estadoClass}'>" . $m['estado'] . "</span></td>";
                                            
                                            echo "<td class='text-nowrap'>";
                                            // Botón para ver detalles
                                            echo "<button class='btn btn-sm btn-outline-primary ver-material me-1' 
                                                    data-id='" . $m['id'] . "' 
                                                    data-nombre='" . htmlspecialchars($m['nombre']) . "' 
                                                    data-unidad='" . htmlspecialchars($m['unidad_medida']) . "' 
                                                    data-costo='" . $m['costo'] . "' 
                                                    data-cantidad='" . $m['cantidad'] . "' 
                                                    data-fecha='" . $m['fecha_creacion'] . "' 
                                                    data-estado='" . $m['estado'] . "'>";
                                            echo "<i class='bi bi-eye'></i></button>";
                                            
                                            // Botón para activar/desactivar con estilo de categoría
                                            echo "<button class='btn btn-sm btn-outline-primary editar-estado-indirecto' 
                                                    data-id='" . $m['id'] . "' 
                                                    data-estado='" . $m['estado'] . "' 
                                                    title='" . ($m['estado'] === 'Activo' ? 'Desactivar' : 'Activar') . "'>";
                                            echo "<i class='bi " . ($m['estado'] === 'Activo' ? 'bi-pause' : 'bi-play') . " me-1'></i>" . ($m['estado'] === 'Activo' ? 'Inactivar' : 'Activar') . "</button>";
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo '<tr><td colspan="8" class="text-center">No hay materiales registrados</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- MODAL AÑADIR MATERIAL -->
<div class="modal fade" id="modalAgregarMaterial" tabindex="-1" aria-labelledby="modalAgregarMaterialLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalAgregarMaterialLabel">Añadir Material Indirecto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="../controllers/guardar_materiales_indirectos.php" method="POST">
          <div class="mb-3">
            <label>Nombre del Material:</label>
            <input type="text" class="form-control" name="nombre" required>
          </div>
          <div class="mb-3">
            <label>Unidad de Medida:</label>
            <select class="form-select" name="unidad_medida" required>
              <option value="">Seleccione...</option>
              <option value="Unidad">Unidad</option>
              <option value="Litro">Litro</option>
              <option value="Metro">Metro</option>
              <option value="Gramo">Gramo</option>
              <option value="Kilogramo">Par</option>
            </select>
          </div>
          <div class="mb-3">
            <label>Costo (COP):</label>
            <input type="number" class="form-control" name="costo" step="0.01" required>
          </div>
          <div class="mb-3">
            <label>Cantidad:</label>
            <input type="number" class="form-control" name="cantidad" min="0" required>
          </div>
          <div class="text-end">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Agregar Material Directo -->
<div class="modal fade" id="modalAgregarMaterialDirecto" tabindex="-1" aria-labelledby="modalAgregarMaterialDirectoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalAgregarMaterialDirectoLabel">Añadir Material Directo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formMaterialDirecto" action="../controllers/guardar_material_directo.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Material <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Unidad de Medida <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="unidad_medida" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Referencia</label>
                                <input type="text" class="form-control" name="referencia">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Horma</label>
                        <input type="text" class="form-control" name="horma">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Género <span class="text-danger">*</span></label>
                                <select class="form-select" name="genero" id="selectGenero" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Hombre">Hombre</option>
                                    <option value="Mujer">Mujer</option>
                                    <option value="Unisex">Unisex</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tallas (separadas por comas) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="inputTallas" name="tallas" placeholder="Ej: 36,38,40,42" required>
                                <small class="text-muted">Ingrese las tallas separadas por comas</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Calibre</label>
                                <input type="text" class="form-control" name="calibre">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ancho (cm)</label>
                                <input type="number" step="0.01" class="form-control" name="ancho">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Alto (cm)</label>
                                <input type="number" step="0.01" class="form-control" name="alto">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Peso (gr)</label>
                                <input type="number" step="0.01" class="form-control" name="peso">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Proveedor <span class="text-danger">*</span></label>
                        <select class="form-select" name="proveedor_id" required>
                            <option value="">Seleccione un proveedor...</option>
                            <?php foreach($proveedores as $prov): ?>
                                <option value="<?= $prov['id'] ?>"><?= htmlspecialchars($prov['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Costo <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control" name="costo" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Imagen</label>
                        <input type="file" class="form-control" name="imagen" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Operaciones <span class="text-danger">*</span></label>
                        <div class="border p-2 rounded" style="max-height: 150px; overflow-y: auto;">
                            <?php foreach($operaciones as $op): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="operaciones[]" value="<?= $op['id'] ?>" id="op<?= $op['id'] ?>">
                                    <label class="form-check-label" for="op<?= $op['id'] ?>">
                                        <?= htmlspecialchars($op['nombre']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                            <div class="input-group mt-2">
                                <input type="text" class="form-control form-control-sm" id="nuevaOperacion" placeholder="Añadir nueva operación">
                                <button class="btn btn-outline-secondary btn-sm" type="button" id="btnAgregarOperacion">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar Material</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Función para manejar la adición de nuevas operaciones
function agregarNuevaOperacion(nombre) {
    if (!nombre.trim()) return;
    
    // Verificar si ya existe
    const existe = Array.from(document.querySelectorAll('.form-check-label'))
        .some(label => label.textContent.trim() === nombre.trim());
    
    if (existe) {
        alert('Esta operación ya existe');
        return;
    }
    
    // Crear nuevo checkbox
    const div = document.createElement('div');
    div.className = 'form-check';
    const id = 'op' + Date.now();
    div.innerHTML = `
        <input class="form-check-input" type="checkbox" name="nuevas_operaciones[]" value="${nombre}" id="${id}" checked>
        <label class="form-check-label" for="${id}">
            ${nombre}
        </label>
    `;
    
    // Insertar antes del input group
    const container = document.querySelector('.modal-body .form-check:last-of-type').parentNode;
    container.insertBefore(div, document.getElementById('nuevaOperacion').parentNode.parentNode);
    
    // Limpiar input
    document.getElementById('nuevaOperacion').value = '';
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Agregar nueva operación
    document.getElementById('btnAgregarOperacion').addEventListener('click', function() {
        const input = document.getElementById('nuevaOperacion');
        agregarNuevaOperacion(input.value);
    });
    
    // Permitir Enter en el input de nueva operación
    document.getElementById('nuevaOperacion').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            agregarNuevaOperacion(this.value);
        }
    });

    // Delegación de eventos para el botón de ver material directo
    document.addEventListener('click', function(e) {
        if (e.target.closest('.ver-material-directo')) {
            const btn = e.target.closest('.ver-material-directo');
            verDetalleMaterialDirecto(btn);
        }
    });
    
    // Delegación de eventos para el botón de cambiar estado directo
    document.addEventListener('click', function(e) {
        if (e.target.closest('.editar-estado-directo')) {
            e.preventDefault();
            const btn = e.target.closest('.editar-estado-directo');
            const id = btn.dataset.id;
            const estadoActual = btn.dataset.estado;
            
            if (confirm(`¿Está seguro que desea ${estadoActual === 'Activo' ? 'desactivar' : 'activar'} este material?`)) {
                cambiarEstadoMaterialDirecto(id, estadoActual);
            }
        }
    });
});

// Función para ver detalles del material directo
function verDetalleMaterialDirecto(btn) {
    const id = btn.dataset.id;
    
    // Mostrar spinner de carga
    const modalBody = document.querySelector('#modalVerMaterial .modal-body');
    if (modalBody) {
        modalBody.innerHTML = `
            <div class="text-center my-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2">Cargando información del material...</p>
            </div>
        `;
        
        // Mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById('modalVerMaterial'));
        modal.show();
        
        // Obtener los datos del material
        fetch(`../controllers/obtener_material_directo.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    modalBody.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            ${data.error}
                        </div>
                    `;
                    return;
                }
                
                // Formatear las tallas
                const tallas = data.tallas_disponibles ? JSON.parse(data.tallas_disponibles) : [];
                const tallasHtml = tallas.length > 0 ? 
                    tallas.map(t => `<span class="badge bg-secondary me-1">${t}</span>`).join('') : 
                    '<span class="text-muted">No especificadas</span>';
                
                // Formatear la imagen
                const imagenHtml = data.imagen_url ? 
                    `<img src="../${data.imagen_url}" class="img-fluid rounded mb-3" style="max-height: 200px;" alt="${data.nombre}">` : 
                    '<div class="text-center py-4 bg-light rounded"><i class="bi bi-image fs-1 text-muted"></i><p class="mt-2">Sin imagen</p></div>';
                
                // Construir el contenido del modal
                const html = `
                    <div class="row">
                        <div class="col-md-5">
                            ${imagenHtml}
                            <h5 class="mt-3">Información Básica</h5>
                            <hr class="mt-1">
                            <p><strong>Nombre:</strong> ${data.nombre || 'N/A'}</p>
                            <p><strong>Referencia:</strong> ${data.referencia || 'N/A'}</p>
                            <p><strong>Descripción:</strong> ${data.descripcion || 'Sin descripción'}</p>
                            <p><strong>Unidad de Medida:</strong> ${data.unidad_medida || 'N/A'}</p>
                            <p><strong>Horma:</strong> ${data.horma || 'N/A'}</p>
                        </div>
                        <div class="col-md-7">
                            <h5>Especificaciones</h5>
                            <hr class="mt-1">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Género:</strong> ${data.genero || 'N/A'}</p>
                                    <p><strong>Calibre:</strong> ${data.calibre || 'N/A'}</p>
                                    <p><strong>Ancho:</strong> ${data.ancho ? data.ancho + ' cm' : 'N/A'}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Alto:</strong> ${data.alto ? data.alto + ' cm' : 'N/A'}</p>
                                    <p><strong>Peso:</strong> ${data.peso ? data.peso + ' gr' : 'N/A'}</p>
                                    <p><strong>Costo:</strong> $${data.costo ? parseFloat(data.costo).toFixed(2) : '0.00'}</p>
                                </div>
                            </div>
                            
                            <h5 class="mt-4">Tallas Disponibles</h5>
                            <hr class="mt-1">
                            <div class="mb-3">
                                ${tallasHtml}
                            </div>
                            
                            <h5 class="mt-4">Proveedor</h5>
                            <hr class="mt-1">
                            <p>${data.proveedor_nombre || 'No especificado'}</p>
                            
                            <div class="text-muted small mt-4">
                                <p class="mb-0"><strong>Fecha de creación:</strong> ${data.fecha_creacion ? new Date(data.fecha_creacion).toLocaleDateString() : 'N/A'}</p>
                                <p class="mb-0"><strong>Estado:</strong> ${data.estado === 'Activo' ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>'}</p>
                            </div>
                        </div>
                    </div>
                `;
                
                modalBody.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Error al cargar la información del material. Por favor, intente nuevamente.
                        <div class="mt-2 small">${error.message}</div>
                    </div>
                `;
            });
    }
}

// Función para ver detalles del material (compatibilidad)
function verDetalleMaterial(id) {
    const btn = document.querySelector(`.ver-material-directo[data-id="${id}"]`);
    if (btn) verDetalleMaterialDirecto(btn);
}

// Función para cambiar el estado de un material directo
function cambiarEstadoMaterialDirecto(id, estadoActual) {
    const nuevoEstado = estadoActual === 'Activo' ? 'Inactivo' : 'Activo';
    
    fetch(`../controllers/cambiar_estado_material_indirecto.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&tipo=directo&estado=${nuevoEstado}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar mensaje de éxito
            const toast = document.createElement('div');
            toast.className = 'position-fixed top-0 end-0 p-3';
            toast.style.zIndex = '1100';
            toast.innerHTML = `
                <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-success text-white">
                        <strong class="me-auto">¡Éxito!</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        El material ha sido ${nuevoEstado === 'Activo' ? 'activado' : 'desactivado'} correctamente.
                    </div>
                </div>
            `;
            document.body.appendChild(toast);
            
            // Actualizar la interfaz sin recargar la página
            const fila = document.querySelector(`tr[data-id="${id}"]`);
            if (fila) {
                // Actualizar el estado en la tabla
                const estadoBadge = fila.querySelector('.estado-badge');
                const btnEstado = fila.querySelector('.editar-estado-directo');
                
                if (estadoBadge) {
                    estadoBadge.className = `badge bg-${nuevoEstado === 'Activo' ? 'success' : 'secondary'}`;
                    estadoBadge.textContent = nuevoEstado;
                }
                
                if (btnEstado) {
                    btnEstado.dataset.estado = nuevoEstado;
                    btnEstado.innerHTML = `
                        <i class="bi ${nuevoEstado === 'Activo' ? 'bi-pause' : 'bi-play'} me-1"></i>
                        ${nuevoEstado === 'Activo' ? 'Inactivar' : 'Activar'}
                    `;
                    btnEstado.title = nuevoEstado === 'Activo' ? 'Desactivar' : 'Activar';
                }
            }
            
            // Ocultar el mensaje después de 3 segundos
            setTimeout(() => {
                toast.remove();
            }, 3000);
            
        } else {
            // Mostrar mensaje de error
            const toast = document.createElement('div');
            toast.className = 'position-fixed top-0 end-0 p-3';
            toast.style.zIndex = '1100';
            toast.innerHTML = `
                <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-danger text-white">
                        <strong class="me-auto">Error</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        Error al actualizar el estado: ${data.error || 'Error desconocido'}
                    </div>
                </div>
            `;
            document.body.appendChild(toast);
            
            // Ocultar el mensaje después de 5 segundos
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Mostrar mensaje de error
        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 end-0 p-3';
        toast.style.zIndex = '1100';
        toast.innerHTML = `
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-danger text-white">
                    <strong class="me-auto">Error</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    Error al conectar con el servidor. Por favor, intente nuevamente.
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        
        // Ocultar el mensaje después de 5 segundos
        setTimeout(() => {
            toast.remove();
        }, 5000);
    });
}

// Función para cambiar el estado de un material directo (compatibilidad)
function cambiarEstadoMaterial(id, estadoActual) {
    cambiarEstadoMaterialDirecto(id, estadoActual);
}

// Función para cambiar el estado de un material indirecto
function cambiarEstadoMaterialIndirecto(id, estadoActual) {
    const nuevoEstado = estadoActual === 'Activo' ? 'Inactivo' : 'Activo';
    
    fetch(`../controllers/cambiar_estado_material_indirecto.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&tipo=indirecto&estado=${nuevoEstado}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar mensaje de éxito
            const toast = document.createElement('div');
            toast.className = 'position-fixed top-0 end-0 p-3';
            toast.style.zIndex = '1100';
            toast.innerHTML = `
                <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-success text-white">
                        <strong class="me-auto">¡Éxito!</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        El material ha sido ${nuevoEstado === 'Activo' ? 'activado' : 'desactivado'} correctamente.
                    </div>
                </div>
            `;
            document.body.appendChild(toast);
            
            // Actualizar la interfaz sin recargar la página
            const fila = document.querySelector(`tr[data-id="${id}"]`);
            if (fila) {
                // Actualizar el estado en la tabla
                const estadoBadge = fila.querySelector('.estado-badge');
                const btnEstado = fila.querySelector('.editar-estado-indirecto');
                
                if (estadoBadge) {
                    estadoBadge.className = `badge bg-${nuevoEstado === 'Activo' ? 'success' : 'secondary'}`;
                    estadoBadge.textContent = nuevoEstado;
                }
                
                if (btnEstado) {
                    btnEstado.dataset.estado = nuevoEstado;
                    btnEstado.innerHTML = `
                        <i class="bi ${nuevoEstado === 'Activo' ? 'bi-pause' : 'bi-play'} me-1"></i>
                        ${nuevoEstado === 'Activo' ? 'Inactivar' : 'Activar'}
                    `;
                    btnEstado.title = nuevoEstado === 'Activo' ? 'Desactivar' : 'Activar';
                }
            }
            
            // Ocultar el mensaje después de 3 segundos
            setTimeout(() => {
                toast.remove();
            }, 3000);
            
        } else {
            // Mostrar mensaje de error
            const toast = document.createElement('div');
            toast.className = 'position-fixed top-0 end-0 p-3';
            toast.style.zIndex = '1100';
            toast.innerHTML = `
                <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-danger text-white">
                        <strong class="me-auto">Error</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        Error al actualizar el estado: ${data.error || 'Error desconocido'}
                    </div>
                </div>
            `;
            document.body.appendChild(toast);
            
            // Ocultar el mensaje después de 5 segundos
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Mostrar mensaje de error
        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 end-0 p-3';
        toast.style.zIndex = '1100';
        toast.innerHTML = `
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-danger text-white">
                    <strong class="me-auto">Error</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    Error al conectar con el servidor. Por favor, intente nuevamente.
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        
        // Ocultar el mensaje después de 5 segundos
        setTimeout(() => {
            toast.remove();
        }, 5000);
    });
}

// Mostrar detalles del material indirecto
function verDetalleMaterialIndirecto(id) {
    const btn = document.querySelector(`.ver-material[data-id="${id}"]`);
    if (!btn) return;
    
    const nombre = btn.dataset.nombre;
    const unidad = btn.dataset.unidad;
    const costo = parseFloat(btn.dataset.costo).toFixed(2);
    const cantidad = btn.dataset.cantidad;
    const fecha = btn.dataset.fecha;
    const estado = btn.dataset.estado;
    
    // Formatear la fecha
    const fechaObj = new Date(fecha);
    const fechaFormateada = fechaObj.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    // Crear el contenido del modal
    const contenido = `
        <div class="row">
            <div class="col-md-6">
                <p><strong>ID:</strong> ${id}</p>
                <p><strong>Nombre:</strong> ${nombre}</p>
                <p><strong>Unidad de Medida:</strong> ${unidad}</p>
                <p><strong>Costo Unitario:</strong> $${costo}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Cantidad:</strong> ${cantidad}</p>
                <p><strong>Fecha de Registro:</strong> ${fechaFormateada}</p>
                <p><strong>Estado:</strong> 
                    <span class="badge bg-${estado === 'Activo' ? 'success' : 'secondary'}">
                        ${estado}
                    </span>
                </p>
            </div>
        </div>
    `;
    
    // Actualizar el contenido del modal
    const modalBody = document.querySelector('#modalVerMaterial .modal-body');
    if (modalBody) {
        modalBody.innerHTML = contenido;
        
        // Mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById('modalVerMaterial'));
        modal.show();
    }
}

// Manejar clic en el botón de ver material indirecto
document.addEventListener('DOMContentLoaded', function() {
    // Delegación de eventos para el botón de ver material
    document.addEventListener('click', function(e) {
        if (e.target.closest('.ver-material')) {
            const btn = e.target.closest('.ver-material');
            const id = btn.dataset.id;
            verDetalleMaterialIndirecto(id);
        }
    });
    
    // Delegación de eventos para el botón de cambiar estado
    document.addEventListener('click', function(e) {
        if (e.target.closest('.editar-estado-indirecto')) {
            e.preventDefault();
            const btn = e.target.closest('.editar-estado-indirecto');
            const id = btn.dataset.id;
            const estadoActual = btn.dataset.estado;
            const nuevoEstado = estadoActual === 'Activo' ? 'Inactivo' : 'Activo';
            
            if (confirm(`¿Está seguro que desea ${estadoActual === 'Activo' ? 'desactivar' : 'activar'} este material?`)) {
                cambiarEstadoMaterialIndirecto(id, estadoActual);
            }
        }
    });
});
</script>

<!-- Modal Ver Material -->
<div class="modal fade" id="modalVerMaterial" tabindex="-1" aria-labelledby="modalVerMaterialLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalVerMaterialLabel">Detalles del Material</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <!-- El contenido se cargará dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
