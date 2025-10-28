<?php
include_once '../includes/header.php';
include_once '../includes/sidebar.php';
?>

<div class="main-content p-4">
    <div class="container-fluid"> <br><br><br>
        <!-- Título principal -->
        <h2 class="fw-bold text-dark mb-4">Gestión de Artículos</h2>

        <!-- Pestañas principales -->
        <ul class="nav nav-tabs mb-4" id="articulosTabs" role="tablist">
            
            <li class="nav-item">
                <a class="nav-link" id="ficha-tab" data-bs-toggle="tab" href="#ficha" role="tab">
                    <i class="bi bi-file-earmark-text"></i> Ficha Técnica
                </a>
            </li>
           
            <li class="nav-item">
                <a class="nav-link" id="procesos-tab" data-bs-toggle="tab" href="#procesos" role="tab">
                    <i class="bi bi-gear"></i> Procesos y Operaciones
                </a>
            </li>
        </ul>

        <!-- Contenido de las pestañas -->
        <div class="tab-content" id="articulosTabsContent">

            <!-- Ficha Técnica -->
            <div class="tab-pane fade" id="ficha" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light fw-bold">
                        <i class="bi bi-file-earmark-text"></i> Ficha Técnica
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Registra y administra las especificaciones técnicas de cada artículo.</p>
                    </div>
                </div>
            </div>

            <!-- Materia Prima -->
            <div class="tab-pane fade" id="materia" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light fw-bold">
                        <i class="bi bi-box-seam"></i> Materia Prima
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Gestiona los insumos y materiales utilizados para la fabricación de artículos.</p>
                    </div>
                </div>
            </div>

            <!-- Procesos y Operaciones -->
            <div class="tab-pane fade" id="procesos" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light fw-bold">
                        <i class="bi bi-gear"></i> Procesos y Operaciones
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Controla las etapas y operaciones involucradas en la producción de los artículos.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
