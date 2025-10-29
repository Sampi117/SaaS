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

            
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
