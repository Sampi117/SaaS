<?php
include_once '../includes/header.php';
include_once '../includes/sidebar.php';
?>

<div class="main-content p-4">
    <div class="container-fluid"> <br><br><br>
        <h2 class="fw-bold text-dark mb-4">Gestión de Categorías</h2>

        <!-- Mensajes -->
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
            <div class="alert alert-primary alert-dismissible fade show" role="alert">
                Categoría guardada correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php elseif(isset($_GET['msg']) && $_GET['msg'] == 'error'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Error al guardar la categoría: <?= htmlspecialchars($_GET['error'] ?? 'Error desconocido') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>

        <!-- Buscador y Añadir Categoría -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <form method="GET" class="input-group w-auto mb-2">
                <input type="text" name="buscar" id="busqueda" class="form-control" placeholder="Buscar por nombre de categoría" value="<?= isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '' ?>">
                <button class="btn btn-outline-primary" type="submit" id="btnBuscar"><i class="bi bi-search"></i></button>
            </form>
            <button class="btn btn-primary d-flex align-items-center mb-2" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                <i class="bi bi-plus-lg me-2"></i> Nueva Categoría
            </button>
        </div>

        <!-- Tabla de categorías -->
        <div class="shadow-sm rounded bg-white">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="table-primary">
                    <tr>
                        <th>Nombre</th>
                        <th>Costos Indirectos</th>
                        <th>Costos Financieros</th>
                        <th>Costos Distribución</th>
                        <th>Total Costos Fijos</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    include("../config/conexion.php");
                    $busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
                    $sql = "SELECT c.*, 
                                   (SELECT COUNT(*) FROM categoria_materiales cm WHERE cm.id_categoria = c.id) as total_materiales
                            FROM categorias c 
                            WHERE c.nombre LIKE :buscar 
                            ORDER BY c.id DESC";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['buscar' => "%$busqueda%"]);
                    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($categorias) > 0) {
                        foreach ($categorias as $cat) {
                            $estadoClass = ($cat['estado'] === 'Activo') ? 'bg-primary' : 'bg-secondary';
                            $totalCostosFijos = $cat['costos_indirectos'] + $cat['costos_financieros'] + $cat['costos_distribucion'];
                            echo '<tr data-id="' . $cat['id'] . '">';
                            echo '<td>' . htmlspecialchars($cat['nombre']) . '</td>';
                            echo '<td>$' . number_format($cat['costos_indirectos'], 2) . '</td>';
                            echo '<td>$' . number_format($cat['costos_financieros'], 2) . '</td>';
                            echo '<td>$' . number_format($cat['costos_distribucion'], 2) . '</td>';
                            echo '<td>$' . number_format($totalCostosFijos, 2) . '</td>';
                            echo '<td><span class="badge ' . $estadoClass . ' estado-badge">' . $cat['estado'] . '</span></td>';
                            echo '<td>
                                    <button class="btn btn-sm btn-outline-primary ver-categoria me-1" 
                                        data-bs-toggle="modal" data-bs-target="#modalVerCategoria"
                                        data-id="' . $cat['id'] . '"
                                        data-nombre="' . htmlspecialchars($cat['nombre']) . '"
                                        data-indirectos="' . $cat['costos_indirectos'] . '"
                                        data-financieros="' . $cat['costos_financieros'] . '"
                                        data-distribucion="' . $cat['costos_distribucion'] . '"
                                        data-total-materiales="' . $cat['total_materiales'] . '"
                                        data-estado="' . $cat['estado'] . '">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary editar-estado" 
                                        data-id="' . $cat['id'] . '" 
                                        data-estado="' . $cat['estado'] . '" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalEditarEstado">
                                        <i class="bi ' . ($cat['estado'] === 'Activo' ? 'bi-pause' : 'bi-play') . '"></i> ' . ($cat['estado'] === 'Activo' ? 'Inactivar' : 'Activar') . '
                                    </button>
                                </td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="7" class="text-center">No hay categorías para mostrar</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div> <br><br>
</div>

<!-- MODAL AGREGAR CATEGORÍA -->
<div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalAgregarLabel">Nueva Categoría</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="formAgregarCategoria" action="../controllers/guardar_categoria.php" method="POST">
          <div class="row g-3">
            <div class="col-md-6">
              <label>Nombre de la Categoría:</label>
              <input type="text" class="form-control" name="nombre" required>
            </div>
            
            <div class="col-md-12">
              <h5 class="mt-4 mb-3">Costos Fijos</h5>
            </div>
            
            <div class="col-md-4">
              <label>Costos Indirectos:</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" class="form-control costos-input" name="costos_indirectos" step="0.01" min="0" value="0.00" required>
              </div>
            </div>
            
            <div class="col-md-4">
              <label>Costos Financieros:</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" class="form-control costos-input" name="costos_financieros" step="0.01" min="0" value="0.00" required>
              </div>
            </div>
            
            <div class="col-md-4">
              <label>Costos de Distribución:</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" class="form-control costos-input" name="costos_distribucion" step="0.01" min="0" value="0.00" required>
              </div>
            </div>
            
            <div class="col-md-12">
              <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
                <h5>Materiales Indirectos</h5>
                  <button type="button" class="btn btn-sm btn-outline-primary" id="btnAbrirBuscarMaterial">
                    <i class="bi bi-plus-lg me-1"></i> Agregar Material
                  </button>
              </div>
              
              <div class="table-responsive">
                <table class="table table-bordered" id="tablaMateriales">
                  <thead class="table-light">
                    <tr>
                      <th>ID</th>
                      <th>Material</th>
                      <th>Unidad</th>
                      <th>Costo Unitario</th>
                      <th>Cantidad</th>
                      <th>Costo Total</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody id="cuerpoTablaMateriales">
                    <!-- Los materiales se agregarán aquí dinámicamente -->
                  </tbody>
                  <tfoot>
                    <tr class="table-active">
                      <td colspan="5" class="text-end fw-bold">Total Materiales:</td>
                      <td id="totalMateriales" class="fw-bold">$0.00</td>
                      <td></td>
                    </tr>
                    <tr class="table-active">
                      <td colspan="5" class="text-end fw-bold">Total Costos Fijos:</td>
                      <td id="totalCostosFijos" class="fw-bold">$0.00</td>
                      <td></td>
                    </tr>
                    <tr class="table-active">
                      <td colspan="5" class="text-end fw-bold">Total General:</td>
                      <td id="totalGeneral" class="fw-bold">$0.00</td>
                      <td></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
          
          <div class="mt-4 text-end">
            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar Categoría</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- MODAL BUSCAR MATERIAL -->
<div class="modal fade" id="modalBuscarMaterial" tabindex="-1" aria-labelledby="modalBuscarMaterialLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalBuscarMaterialLabel">
            <i class="bi bi-search me-2"></i>Buscar y Agregar Materiales
        </h5>
        <button type="button" class="btn-close btn-close-white" id="btnCerrarModalBusqueda" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info alert-dismissible fade show" role="alert">
          <i class="bi bi-info-circle me-2"></i>
          <strong>Tip:</strong> Puedes agregar múltiples materiales. Los materiales se agregarán a tu categoría sin cerrar esta ventana.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        
        <div class="mb-3">
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="buscadorMaterial" class="form-control" placeholder="Buscar material por nombre o ID...">
            <button class="btn btn-outline-secondary" type="button" id="btnLimpiarBusqueda">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
        </div>
        
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
          <table class="table table-hover table-sm">
            <thead class="table-light sticky-top">
              <tr>
                <th style="width: 60px;">ID</th>
                <th>Nombre</th>
                <th style="width: 100px;">Unidad</th>
                <th style="width: 120px;">Costo</th>
                <th style="width: 120px;" class="text-center">Acción</th>
              </tr>
            </thead>
            <tbody id="resultadosMateriales">
              <tr>
                <td colspan="5" class="text-center py-3">
                  <i class="bi bi-hourglass-split text-muted"></i>
                  <p class="mb-0 mt-2">Los materiales se cargarán al abrir esta ventana</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="btnFinalizarAgregar">
          <i class="bi bi-check-lg me-1"></i>Finalizar y Cerrar
        </button>
      </div>
    </div>
  </div>
</div>


<!-- MODAL VER CATEGORÍA -->
<div class="modal fade" id="modalVerCategoria" tabindex="-1" aria-labelledby="modalVerCategoriaLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalVerCategoriaLabel">Detalles de la Categoría</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-4">
          <div class="col-md-6">
            <h5>Información General</h5>
            <table class="table table-bordered">
              <tr><th class="w-50">Nombre:</th><td id="ver_nombre"></td></tr>
              <tr><th>Estado:</th><td id="ver_estado"></td></tr>
            </table>
          </div>
          <div class="col-md-6">
            <h5>Costos Fijos</h5>
            <table class="table table-bordered">
              <tr><th class="w-50">Costos Indirectos:</th><td id="ver_indirectos"></td></tr>
              <tr><th>Costos Financieros:</th><td id="ver_financieros"></td></tr>
              <tr><th>Costos de Distribución:</th><td id="ver_distribucion"></td></tr>
              <tr class="table-active"><th>Total Costos Fijos:</th><td id="ver_total_fijos" class="fw-bold"></td></tr>
            </table>
          </div>
        </div>
        
        <div class="mt-4">
          <h5>Materiales Indirectos <span id="ver_total_materiales" class="badge bg-primary"></span></h5>
          <div class="table-responsive">
            <table class="table table-bordered" id="tablaMaterialesDetalle">
              <thead class="table-light">
                <tr>
                  <th>ID</th>
                  <th>Material</th>
                  <th>Unidad</th>
                  <th>Costo Unitario</th>
                  <th>Cantidad</th>
                  <th>Costo Total</th>
                </tr>
              </thead>
              <tbody id="cuerpoTablaMaterialesDetalle">
                <!-- Los materiales se cargarán aquí dinámicamente -->
              </tbody>
              <tfoot>
                <tr class="table-active">
                  <td colspan="5" class="text-end fw-bold">Total Materiales:</td>
                  <td id="ver_total_materiales_costo" class="fw-bold">$0.00</td>
                </tr>
                <tr class="table-active">
                  <td colspan="5" class="text-end fw-bold">Total General:</td>
                  <td id="ver_total_general" class="fw-bold">$0.00</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL EDITAR ESTADO -->
<div class="modal fade" id="modalEditarEstado" tabindex="-1" aria-labelledby="modalEditarEstadoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalEditarEstadoLabel">Cambiar Estado de la Categoría</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="formEditarEstadoCategoria">
          <input type="hidden" id="editar_id_categoria">
          <div class="mb-3">
            <label for="editar_estado_categoria" class="form-label">Nuevo Estado:</label>
            <select class="form-select" id="editar_estado_categoria" required>
              <option value="Activo">Activar</option>
              <option value="Inactivo">Inactivar</option>
            </select>
          </div>
          <div class="text-end">
            <button type="button" class="btn btn-primary" id="guardarEstadoCategoria">Guardar Cambios</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", function() {
    // Variables globales
    let materialesAgregados = [];
    
    // Función para formatear moneda
    function formatoMoneda(valor) {
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(valor);
    }
    
    // Función para actualizar totales
    function actualizarTotales() {
        // Calcular total de materiales
        let totalMateriales = 0;
        document.querySelectorAll('.costo-total').forEach(celda => {
            totalMateriales += parseFloat(celda.dataset.valor) || 0;
        });
        
        // Calcular total de costos fijos
        const costosIndirectos = parseFloat(document.querySelector('input[name="costos_indirectos"]').value) || 0;
        const costosFinancieros = parseFloat(document.querySelector('input[name="costos_financieros"]').value) || 0;
        const costosDistribucion = parseFloat(document.querySelector('input[name="costos_distribucion"]').value) || 0;
        const totalCostosFijos = costosIndirectos + costosFinancieros + costosDistribucion;
        
        // Actualizar totales en la tabla
        document.getElementById('totalMateriales').textContent = formatoMoneda(totalMateriales);
        document.getElementById('totalCostosFijos').textContent = formatoMoneda(totalCostosFijos);
        document.getElementById('totalGeneral').textContent = formatoMoneda(totalMateriales + totalCostosFijos);
    }
    
    // Actualizar totales cuando cambian los costos fijos
    document.querySelectorAll('.costos-input').forEach(input => {
        input.addEventListener('change', actualizarTotales);
    });
    
    // Función para mostrar alertas
    function mostrarAlerta(mensaje, tipo = 'danger') {
        const alerta = document.createElement('div');
        alerta.className = `alert alert-${tipo} alert-dismissible fade show`;
        alerta.role = 'alert';
        alerta.innerHTML = `
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        `;
        
        // Insertar la alerta al inicio del contenido principal
        const mainContent = document.querySelector('.main-content .container-fluid');
        if (mainContent && mainContent.firstChild) {
            mainContent.insertBefore(alerta, mainContent.firstChild);
        } else if (mainContent) {
            mainContent.appendChild(alerta);
        }
        
        // Eliminar la alerta después de 5 segundos
        setTimeout(() => {
            alerta.remove();
        }, 5000);
    }
    
    // Función para agregar material a la tabla
    function agregarMaterial(id, nombre, unidad, costo) {
        // Verificar si el material ya fue agregado
        if (materialesAgregados.includes(id.toString())) {
            mostrarAlerta('Este material ya ha sido agregado', 'warning');
            return;
        }
        
        // Agregar a la lista de materiales agregados
        materialesAgregados.push(id.toString());
        
        // Crear fila en la tabla de materiales
        const filaId = `material-${id}`;
        const fila = document.createElement('tr');
        fila.id = filaId;
        fila.innerHTML = `
            <td>${id}</td>
            <td>${nombre}</td>
            <td>${unidad || 'N/A'}</td>
            <td>${formatoMoneda(costo)}</td>
            <td>
                <input type="number" class="form-control form-control-sm cantidad-material" 
                       data-id="${id}" 
                       data-costo="${costo}"
                       value="1" 
                       min="0.001" 
                       step="0.001" 
                       style="width: 100px;">
            </td>
            <td class="costo-total" data-valor="${costo}">${formatoMoneda(costo)}</td>
            <td>
                <button type="button" class="btn btn-sm btn-danger btn-eliminar-material" data-id="${id}">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        
        // Agregar la fila a la tabla
        document.getElementById('cuerpoTablaMateriales').appendChild(fila);
        
        // Agregar evento para actualizar totales cuando cambia la cantidad
        const inputCantidad = fila.querySelector('.cantidad-material');
        inputCantidad.addEventListener('change', function() {
            const cantidad = parseFloat(this.value) || 0;
            const costoUnitario = parseFloat(this.dataset.costo);
            const costoTotal = cantidad * costoUnitario;
            
            // Actualizar el total en la fila
            const celdaTotal = this.closest('tr').querySelector('.costo-total');
            celdaTotal.textContent = formatoMoneda(costoTotal);
            celdaTotal.dataset.valor = costoTotal;
            
            // Actualizar totales
            actualizarTotales();
        });
        
        // Actualizar totales
        actualizarTotales();
        
        // NO cerrar el modal - permitir agregar más materiales
        // Mostrar mensaje de éxito temporal
        mostrarAlerta('Material agregado correctamente. Puedes seguir agregando más materiales.', 'success');
    }
    
    // Función para buscar materiales
    function buscarMateriales(termino = '') {
        const resultados = document.getElementById('resultadosMateriales');
        
        // Mostrar indicador de carga
        resultados.innerHTML = `
            <tr>
                <td colspan="5" class="text-center">
                    <div class="d-flex justify-content-center align-items-center py-3">
                        <div class="spinner-border text-primary me-2" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <span>${termino ? 'Buscando materiales...' : 'Cargando materiales...'}</span>
                    </div>
                </td>
            </tr>`;
        
        // Realizar la petición AJAX
        fetch(`../controllers/buscar_materiales_indirectos.php?q=${encodeURIComponent(termino)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Datos recibidos:', data);
                
                if (!data.success) {
                    throw new Error(data.message || 'Error al buscar materiales');
                }
                
                if (!data.data || data.data.length === 0) {
                    resultados.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="bi bi-search text-muted" style="font-size: 2rem;"></i>
                                <p class="mt-2 mb-0">${termino ? `No se encontraron materiales que coincidan con "${termino}"` : 'No hay materiales disponibles'}</p>
                            </td>
                        </tr>`;
                    return;
                }
                
                resultados.innerHTML = '';
                
                data.data.forEach(material => {
                    const materialId = material.id.toString();
                    const yaAgregado = materialesAgregados.includes(materialId);
                    const nombre = material.nombre || 'Sin nombre';
                    const unidad = material.unidad || material.unidad_medida || 'N/A';
                    const costo = parseFloat(material.costo_unitario || material.costo || 0);
                    
                    const fila = document.createElement('tr');
                    fila.className = 'align-middle';
                    fila.innerHTML = `
                        <td class="fw-bold">${materialId}</td>
                        <td>${nombre}</td>
                        <td>${unidad}</td>
                        <td>${formatoMoneda(costo)}</td>
                        <td class="text-center">
                            ${yaAgregado 
                                ? '<span class="badge bg-secondary"><i class="bi bi-check-lg"></i> Agregado</span>'
                                : `<button class="btn btn-sm btn-primary btn-agregar-material" 
                                       data-id="${materialId}"
                                       data-nombre="${nombre.replace(/"/g, '&quot;')}"
                                       data-unidad="${unidad.replace(/"/g, '&quot;')}"
                                       data-costo="${costo}">
                                    <i class="bi bi-plus-lg"></i> Agregar
                                </button>`
                            }
                        </td>
                    `;
                    resultados.appendChild(fila);
                });
                
                // Agregar event listeners a los botones de agregar
                document.querySelectorAll('.btn-agregar-material').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const id = this.dataset.id;
                        const nombre = this.dataset.nombre;
                        const unidad = this.dataset.unidad;
                        const costo = parseFloat(this.dataset.costo || 0);
                        
                        // Deshabilitar botón y cambiar apariencia
                        this.disabled = true;
                        this.innerHTML = '<i class="bi bi-check-lg"></i> Agregado';
                        this.classList.remove('btn-primary');
                        this.classList.add('btn-secondary');
                        
                        // Agregar material a la tabla
                        agregarMaterial(id, nombre, unidad, costo);
                    });
                });
                
            })
            .catch(error => {
                console.error('Error al buscar materiales:', error);
                resultados.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-danger">
                            <i class="bi bi-exclamation-triangle"></i> ${error.message || 'Error al cargar los materiales'}
                        </td>
                    </tr>`;
            });
    }
    
    // Evento para buscar materiales al escribir
    const buscadorMaterial = document.getElementById('buscadorMaterial');
    let timeoutBusqueda;
    
    if (buscadorMaterial) {
        buscadorMaterial.addEventListener('input', function() {
            const termino = this.value.trim();
            clearTimeout(timeoutBusqueda);
            
            if (termino.length === 0) {
                // Si el campo está vacío, cargar todos los materiales
                buscarMateriales();
                return;
            }
            
            if (termino.length < 2) {
                return; // No buscar con menos de 2 caracteres
            }
            
            // Esperar 300ms después de que el usuario deje de escribir
            timeoutBusqueda = setTimeout(() => {
                buscarMateriales(termino);
            }, 300);
        });
    }
    
    // Botón para limpiar búsqueda
    const btnLimpiarBusqueda = document.getElementById('btnLimpiarBusqueda');
    if (btnLimpiarBusqueda) {
        btnLimpiarBusqueda.addEventListener('click', function() {
            if (buscadorMaterial) {
                buscadorMaterial.value = '';
                buscadorMaterial.focus();
                buscarMateriales();
            }
        });
    }
    
    // Manejar botón "Agregar Material" manualmente para prevenir conflictos
    const btnAbrirBuscarMaterial = document.getElementById('btnAbrirBuscarMaterial');
    if (btnAbrirBuscarMaterial) {
        btnAbrirBuscarMaterial.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Click en Agregar Material');
            window.abriendoModalBusqueda = true;
            
            const modalBuscar = new bootstrap.Modal(document.getElementById('modalBuscarMaterial'));
            modalBuscar.show();
        });
    }
    
    // Cargar materiales al abrir el modal
    const modalBuscarMaterial = document.getElementById('modalBuscarMaterial');
    if (modalBuscarMaterial) {
        modalBuscarMaterial.addEventListener('show.bs.modal', function() {
            console.log('Abriendo modal de búsqueda...');
            window.abriendoModalBusqueda = true;
        });
        
        modalBuscarMaterial.addEventListener('shown.bs.modal', function() {
            console.log('Modal de búsqueda mostrado');
            window.abriendoModalBusqueda = false;
            buscarMateriales();
        });
        
        // Limpiar el buscador al cerrar el modal
        modalBuscarMaterial.addEventListener('hidden.bs.modal', function() {
            if (buscadorMaterial) {
                buscadorMaterial.value = '';
            }
        });
    }
    
    // Manejar cierre del modal de búsqueda SIN cerrar el modal padre
    const btnCerrarModalBusqueda = document.getElementById('btnCerrarModalBusqueda');
    const btnFinalizarAgregar = document.getElementById('btnFinalizarAgregar');
    
    function cerrarModalBusqueda(e) {
        if (e) e.preventDefault();
        
        const modalBuscar = bootstrap.Modal.getInstance(document.getElementById('modalBuscarMaterial'));
        if (modalBuscar) {
            modalBuscar.hide();
        }
        
        // Prevenir que se cierre el modal padre
        setTimeout(() => {
            const modalAgregar = document.getElementById('modalAgregar');
            if (modalAgregar) {
                // Remover backdrop si quedó
                document.querySelectorAll('.modal-backdrop').forEach((backdrop, index) => {
                    if (index > 0) backdrop.remove(); // Mantener solo el primer backdrop
                });
                
                // Asegurar que el modal padre esté visible
                if (!modalAgregar.classList.contains('show')) {
                    modalAgregar.classList.add('show');
                    modalAgregar.style.display = 'block';
                    document.body.classList.add('modal-open');
                }
            }
        }, 100);
    }
    
    if (btnCerrarModalBusqueda) {
        btnCerrarModalBusqueda.addEventListener('click', cerrarModalBusqueda);
    }
    
    if (btnFinalizarAgregar) {
        btnFinalizarAgregar.addEventListener('click', cerrarModalBusqueda);
    }
    
    // Prevenir cierre del modal padre cuando se cierra el modal de búsqueda
    if (modalBuscarMaterial) {
        modalBuscarMaterial.addEventListener('hide.bs.modal', function(e) {
            // Marcar que estamos cerrando el modal de búsqueda
            window.cerrandoModalBusqueda = true;
            console.log('Cerrando modal de búsqueda...');
        });
        
        modalBuscarMaterial.addEventListener('hidden.bs.modal', function(e) {
            console.log('Modal de búsqueda cerrado');
            // Después de cerrar el modal de búsqueda, restaurar el modal padre
            setTimeout(() => {
                const modalAgregar = document.getElementById('modalAgregar');
                if (modalAgregar && window.cerrandoModalBusqueda) {
                    console.log('Restaurando modal padre...');
                    
                    // Asegurar que el modal padre permanezca visible
                    if (!modalAgregar.classList.contains('show')) {
                        modalAgregar.classList.add('show');
                        modalAgregar.style.display = 'block';
                    }
                    
                    // Mantener el body con modal-open
                    document.body.classList.add('modal-open');
                    
                    // Asegurar que hay un backdrop
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    if (backdrops.length === 0) {
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        document.body.appendChild(backdrop);
                    } else if (backdrops.length > 1) {
                        // Si hay más de un backdrop, eliminar los extras
                        for (let i = 1; i < backdrops.length; i++) {
                            backdrops[i].remove();
                        }
                    }
                    
                    // Resetear la bandera después de un momento
                    setTimeout(() => {
                        window.cerrandoModalBusqueda = false;
                        console.log('Bandera reseteada');
                    }, 300);
                }
            }, 50);
        });
    }
    
    // Eliminar material usando delegación de eventos
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-eliminar-material')) {
            const btn = e.target.closest('.btn-eliminar-material');
            const id = btn.dataset.id;
            const fila = document.getElementById(`material-${id}`);
            
            if (fila) {
                // Eliminar de la lista de materiales agregados
                const index = materialesAgregados.indexOf(id);
                if (index > -1) {
                    materialesAgregados.splice(index, 1);
                }
                
                // Eliminar la fila
                fila.remove();
                
                // Actualizar totales
                actualizarTotales();
                
                // Actualizar el botón en el modal de búsqueda si está visible
                const botonAgregar = document.querySelector(`.btn-agregar-material[data-id="${id}"]`);
                if (botonAgregar) {
                    botonAgregar.disabled = false;
                    botonAgregar.innerHTML = '<i class="bi bi-plus-lg"></i> Agregar';
                    botonAgregar.classList.remove('btn-secondary');
                    botonAgregar.classList.add('btn-primary');
                }
            }
        }
    });
    
    // Manejar el envío del formulario de agregar categoría
    const formAgregarCategoria = document.getElementById('formAgregarCategoria');
    if (formAgregarCategoria) {
        formAgregarCategoria.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar que haya al menos un nombre
            const nombre = document.querySelector('input[name="nombre"]').value.trim();
            if (!nombre) {
                mostrarAlerta('El nombre de la categoría es obligatorio', 'danger');
                return;
            }
            
            // Preparar datos de materiales
            const materialesData = [];
            document.querySelectorAll('#cuerpoTablaMateriales tr').forEach(fila => {
                const inputCantidad = fila.querySelector('.cantidad-material');
                if (inputCantidad) {
                    const id = inputCantidad.dataset.id;
                    const cantidad = inputCantidad.value;
                    
                    if (id && cantidad) {
                        materialesData.push({
                            id: parseInt(id),
                            cantidad: parseFloat(cantidad)
                        });
                    }
                }
            });
            
            console.log('Materiales a enviar:', materialesData);
            
            // Crear FormData
            const formData = new FormData(this);
            formData.append('materiales', JSON.stringify(materialesData));
            
            // Mostrar datos en consola para debug
            console.log('Datos del formulario:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            
            // Deshabilitar botón de envío
            const btnSubmit = this.querySelector('button[type="submit"]');
            const textoOriginal = btnSubmit.innerHTML;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
            
            // Enviar datos
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Respuesta del servidor:', response);
                
                // Si hay redirección, la página se recargará automáticamente
                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }
                
                // Si no hay redirección, procesar como JSON
                return response.text().then(text => {
                    console.log('Respuesta texto:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        // Si no es JSON, es probable que sea HTML de redirección
                        console.log('No es JSON, asumiendo éxito');
                        window.location.href = '../view/categoria.php?msg=success';
                        return null;
                    }
                });
            })
            .then(data => {
                if (!data) return; // Ya se redirigió
                
                if (data.success) {
                    window.location.href = '../view/categoria.php?msg=success';
                } else {
                    throw new Error(data.message || 'Error al guardar');
                }
            })
            .catch(error => {
                console.error('Error completo:', error);
                mostrarAlerta('Error al guardar la categoría: ' + error.message, 'danger');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = textoOriginal;
            });
        });
    }
    
    // Ver detalles de la categoría
    document.querySelectorAll('.ver-categoria').forEach(btn => {
        btn.addEventListener('click', function() {
            const idCategoria = this.dataset.id;
            
            // Mostrar indicador de carga
            const cuerpoTabla = document.getElementById('cuerpoTablaMaterialesDetalle');
            if (cuerpoTabla) {
                cuerpoTabla.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </td>
                    </tr>`;
            }
            
            // Obtener datos de la categoría y sus materiales
            fetch(`../controllers/obtener_categoria.php?id=${idCategoria}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error al cargar los datos de la categoría');
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Error al obtener los datos');
                    }
                    
                    const categoria = data.categoria;
                    const materiales = data.materiales || [];
                    
                    // Actualizar información general
                    const verNombre = document.getElementById('ver_nombre');
                    const verEstado = document.getElementById('ver_estado');
                    
                    if (verNombre) verNombre.textContent = categoria.nombre || 'Sin nombre';
                    if (verEstado) {
                        verEstado.innerHTML = `
                            <span class="badge ${categoria.estado === 'Activo' ? 'bg-primary' : 'bg-secondary'}">
                                ${categoria.estado || 'Desconocido'}
                            </span>
                        `;
                    }
                    
                    // Actualizar costos fijos
                    const indirectos = parseFloat(categoria.costos_indirectos) || 0;
                    const financieros = parseFloat(categoria.costos_financieros) || 0;
                    const distribucion = parseFloat(categoria.costos_distribucion) || 0;
                    const totalFijos = indirectos + financieros + distribucion;
                    
                    const verIndirectos = document.getElementById('ver_indirectos');
                    const verFinancieros = document.getElementById('ver_financieros');
                    const verDistribucion = document.getElementById('ver_distribucion');
                    const verTotalFijos = document.getElementById('ver_total_fijos');
                    
                    if (verIndirectos) verIndirectos.textContent = formatoMoneda(indirectos);
                    if (verFinancieros) verFinancieros.textContent = formatoMoneda(financieros);
                    if (verDistribucion) verDistribucion.textContent = formatoMoneda(distribucion);
                    if (verTotalFijos) verTotalFijos.textContent = formatoMoneda(totalFijos);
                    
                    // Mostrar cantidad de materiales
                    const totalMateriales = parseFloat(data.total_materiales) || 0;
                    const verTotalMaterialesBadge = document.getElementById('ver_total_materiales');
                    if (verTotalMaterialesBadge) {
                        verTotalMaterialesBadge.textContent = `${materiales.length} material${materiales.length !== 1 ? 'es' : ''}`;
                    }
                    
                    // Mostrar materiales
                    if (cuerpoTabla) {
                        cuerpoTabla.innerHTML = '';
                        
                        if (materiales.length === 0) {
                            cuerpoTabla.innerHTML = `
                                <tr>
                                    <td colspan="6" class="text-center py-3">No se encontraron materiales asociados a esta categoría.</td>
                                </tr>`;
                        } else {
                            materiales.forEach(material => {
                                const fila = document.createElement('tr');
                                fila.innerHTML = `
                                    <td>${material.id}</td>
                                    <td>${material.nombre || 'Sin nombre'}</td>
                                    <td>${material.unidad || 'N/A'}</td>
                                    <td>${formatoMoneda(parseFloat(material.costo_unitario) || 0)}</td>
                                    <td>${parseFloat(material.cantidad || 0).toFixed(3)}</td>
                                    <td>${formatoMoneda(parseFloat(material.costo_total) || 0)}</td>
                                `;
                                cuerpoTabla.appendChild(fila);
                            });
                        }
                    }
                    
                    // Actualizar totales finales
                    const verTotalMaterialesCosto = document.getElementById('ver_total_materiales_costo');
                    const verTotalGeneral = document.getElementById('ver_total_general');
                    
                    if (verTotalMaterialesCosto) {
                        verTotalMaterialesCosto.textContent = formatoMoneda(totalMateriales);
                    }
                    if (verTotalGeneral) {
                        verTotalGeneral.textContent = formatoMoneda(totalMateriales + totalFijos);
                    }
                    
                })
                .catch(error => {
                    console.error('Error al cargar la categoría:', error);
                    if (cuerpoTabla) {
                        cuerpoTabla.innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center text-danger">
                                    <i class="bi bi-exclamation-triangle"></i> ${error.message || 'Error al cargar los datos'}
                                </td>
                            </tr>`;
                    }
                });
        });
    });
    
    // Editar estado de categoría
    document.querySelectorAll('.editar-estado').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const estadoActual = this.dataset.estado;
            
            const editarId = document.getElementById('editar_id_categoria');
            const editarEstado = document.getElementById('editar_estado_categoria');
            
            if (editarId) editarId.value = id;
            if (editarEstado) {
                editarEstado.value = estadoActual === 'Activo' ? 'Inactivo' : 'Activo';
            }
        });
    });
    
    // Guardar cambios de estado
    const btnGuardarEstado = document.getElementById('guardarEstadoCategoria');
    if (btnGuardarEstado) {
        btnGuardarEstado.addEventListener('click', function() {
            const id = document.getElementById('editar_id_categoria').value;
            const nuevoEstado = document.getElementById('editar_estado_categoria').value;
            
            if (!id || !nuevoEstado) {
                mostrarAlerta('Datos incompletos para actualizar el estado', 'danger');
                return;
            }
            
            // Deshabilitar botón
            const textoOriginal = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
            
            fetch('../controllers/actualizar_estado_categoria.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}&estado=${nuevoEstado}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Recargar la página para ver los cambios
                    window.location.reload();
                } else {
                    mostrarAlerta('Error al actualizar el estado: ' + (data.message || 'Error desconocido'), 'danger');
                    this.disabled = false;
                    this.innerHTML = textoOriginal;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarAlerta('Error al actualizar el estado', 'danger');
                this.disabled = false;
                this.innerHTML = textoOriginal;
            });
        });
    }
    
    // Limpiar formulario SOLO cuando se cierra el modal principal de forma explícita
    const modalAgregar = document.getElementById('modalAgregar');
    if (modalAgregar) {
        modalAgregar.addEventListener('hidden.bs.modal', function(e) {
            console.log('Evento hidden.bs.modal del modal principal', 'cerrandoModalBusqueda:', window.cerrandoModalBusqueda);
            
            // CRÍTICO: Solo limpiar si NO venimos del modal de búsqueda
            if (window.cerrandoModalBusqueda === true) {
                console.log('NO limpiando - viene de modal de búsqueda');
                return; // NO hacer nada si viene del modal de búsqueda
            }
            
            console.log('Limpiando formulario - cierre explícito del modal');
            
            // Limpiar formulario
            if (formAgregarCategoria) {
                formAgregarCategoria.reset();
            }
            
            // Limpiar tabla de materiales
            const cuerpoTabla = document.getElementById('cuerpoTablaMateriales');
            if (cuerpoTabla) {
                cuerpoTabla.innerHTML = '';
            }
            
            // Limpiar lista de materiales agregados
            materialesAgregados = [];
            
            // Actualizar totales
            actualizarTotales();
        });
        
        // También prevenir que se dispare hidden cuando se abre el modal de búsqueda
        modalAgregar.addEventListener('hide.bs.modal', function(e) {
            // Si estamos abriendo el modal de búsqueda, prevenir el cierre del modal padre
            if (window.abriendoModalBusqueda === true) {
                console.log('Previniendo cierre del modal padre');
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
    }
    
    // Inicializar tooltips si existen
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltipTriggerList.length > 0) {
        [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    }
    
    // Inicializar popovers si existen
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    if (popoverTriggerList.length > 0) {
        [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    }
});
</script>

<?php include("../includes/footer.php"); ?>