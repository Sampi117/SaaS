<?php
include_once '../includes/header.php';
include_once '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container py-5">
        <h2 class="text-center mb-4 text-primary fw-bold">Panel Principal</h2>

        <!-- 🔍 Buscador -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-6">
                <input type="text" id="buscador" class="form-control shadow-sm" placeholder="Buscar sección...">
            </div>
        </div>

        <!-- 🔘 Botones -->
        <div class="row g-4 justify-content-center" id="contenedor-botones">
            <?php
            $secciones = [
                ['nombre' => 'Articulos', 'icono' => 'bi-box', 'link' => 'articulos.php'],
                ['nombre' => 'Procesos y Operaciones', 'icono' => 'bi bi-gear', 'link' => '../view/procesos.php'],
                ['nombre' => 'Categoria', 'icono' => 'bi-person-lines-fill', 'link' => '../view/categoria.php'],
                ['nombre' => 'Clientes', 'icono' => 'bi-people', 'link' => '../view/clientes.php'],
                ['nombre' => 'Compras', 'icono' => 'bi-cart-check', 'link' => '../view/compras.php'],
                ['nombre' => 'Materiales', 'icono' => 'bi-graph-up', 'link' => '../view/materiales.php'], 
                ['nombre' => 'Personal', 'icono' => 'bi-person-workspace', 'link' => '../view/personal.php'],
                ['nombre' => 'Produccion', 'icono' => 'bi-gear', 'link' => '../view/produccion.php'],
                ['nombre' => 'Proveedores', 'icono' => 'bi-truck', 'link' => '../view/proveedores.php'],
                /* ['nombre' => 'Ventas', 'icono' => 'bi-cash-stack', 'link' => '../view/ventas.php'], */
            ];

            foreach ($secciones as $sec) {
                echo '
                <div class="col-6 col-md-4 col-lg-3 boton-seccion">
                    <a href="'.$sec['link'].'" class="text-decoration-none">
                        <div class="card-btn text-center p-4">
                            <i class="bi '.$sec['icono'].' icono mb-2"></i>
                            <h6>'.$sec['nombre'].'</h6>
                        </div>
                    </a>
                </div>';
            }
            ?>
        </div>
    </div>
</div>

<!-- 🧠 Script de búsqueda -->
<script>
document.getElementById('buscador').addEventListener('keyup', function() {
    const filtro = this.value.toLowerCase();
    const botones = document.querySelectorAll('.boton-seccion');

    botones.forEach(boton => {
        const nombre = boton.textContent.toLowerCase();
        boton.style.display = nombre.includes(filtro) ? '' : 'none';
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>
