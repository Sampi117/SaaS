<?php
include_once '../includes/header.php';
include_once '../includes/sidebar.php';
?>

<div class="main-content p-4">
    <div class="container-fluid"> <br><br><br>
        <!-- Título principal -->
        <h2 class="fw-bold text-dark mb-4">Gestión de Materiales</h2>

        <ul class="nav nav-tabs mb-4" id="articulosTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link" id="materia-tab" data-bs-toggle="tab" href="#materia" role="tab">
                    <i class="bi bi-box-seam"></i> Materia Prima
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" id="materiales-tab" data-bs-toggle="tab" href="#materiales" role="tab">
                    <i class="bi bi-tools"></i> Materiales Indirectos
                </a>
            </li>
        </ul>

        <div class="tab-content" id="articulosTabsContent">

            <!-- === TAB MATERIA PRIMA === -->
            <div class="tab-pane fade" id="materia" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light fw-bold">
                        <i class="bi bi-box-seam"></i> Materia Prima
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Gestiona los insumos utilizados en la fabricación.</p>
                    </div>
                </div>
            </div>

            <!-- === TAB MATERIALES INDIRECTOS === -->
            <div class="tab-pane fade show active" id="materiales" role="tabpanel">
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
                                            echo "<td>" . $m['id'] . "</td>";
                                            echo "<td>" . htmlspecialchars($m['nombre']) . "</td>";
                                            echo "<td>" . htmlspecialchars($m['unidad_medida']) . "</td>";
                                            echo "<td>$" . number_format($m['costo'], 2, ',', '.') . "</td>";
                                            echo "<td>" . $m['cantidad'] . "</td>";
                                            echo "<td>" . $m['fecha_creacion'] . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo '<tr><td colspan="6" class="text-center">No hay materiales registrados</td></tr>';
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
        <form action="../controllers/guardar_material.php" method="POST">
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

<?php include_once '../includes/footer.php'; ?>
