<?php
include_once '../includes/header.php';
include_once '../includes/sidebar.php';
?>

<div class="main-content p-4">
    <div class="container-fluid"> <br><br><br>
        <!-- Título principal -->
        <h2 class="fw-bold text-dark mb-4">Gestión de Compras</h2>

        <!-- Pestañas principales -->
        <ul class="nav nav-tabs mb-4" id="comprasTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="facturacion-tab" data-bs-toggle="tab" href="#facturacion" role="tab">
                    <i class="bi bi-receipt"></i> Facturación de Compras
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="inventario-tab" data-bs-toggle="tab" href="#inventario" role="tab">
                    <i class="bi bi-box-seam"></i> Inventario
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="orden-tab" data-bs-toggle="tab" href="#orden" role="tab">
                    <i class="bi bi-clipboard-check"></i> Orden de Compra
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="remision-tab" data-bs-toggle="tab" href="#remision" role="tab">
                    <i class="bi bi-truck"></i> Remisión de Compra
                </a>
            </li>
        </ul>

        <!-- Contenido de las pestañas -->
        <div class="tab-content" id="comprasTabsContent">
            <!-- Facturación de Compras -->
            <div class="tab-pane fade show active" id="facturacion" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header fw-bold">
                        <i class="bi bi-receipt"></i> Facturación de Compras
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Registra y gestiona las facturas de tus compras realizadas a proveedores.</p>
                        <!-- Aquí podrás agregar la tabla o formulario de facturas -->
                    </div>
                </div>
            </div>

            <!-- Inventario -->
            <div class="tab-pane fade" id="inventario" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header fw-bold">
                        <i class="bi bi-box-seam"></i> Control de Inventario
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Consulta, actualiza y controla el stock de los productos comprados.</p>
                    </div>
                </div>
            </div>

            <!-- Orden de Compra -->
            <div class="tab-pane fade" id="orden" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header fw-bold">
                        <i class="bi bi-clipboard-check"></i> Orden de Compra
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Crea, administra y realiza seguimiento a las órdenes de compra emitidas.</p>
                    </div>
                </div>
            </div>

            <!-- Remisión de Compra -->
            <div class="tab-pane fade" id="remision" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header fw-bold">
                        <i class="bi bi-truck"></i> Remisión de Compra
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Administra las remisiones y entregas de mercancías recibidas de los proveedores.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
