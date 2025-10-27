<?php
$menu = [
    ['nombre' => 'Página principal', 'icono' => 'bi-house-door', 'link' => '../view/PaginaPrincipal.php'],
    ['nombre' => 'Articulos', 'icono' => 'bi-box', 'link' => '../view/articulos.php'],
    ['nombre' => 'Categoria', 'icono' => 'bi-wallet2', 'link' => '../view/categoria.php'], 
    ['nombre' => 'Clientes', 'icono' => 'bi-people', 'link' => '../view/clientes.php'],
    ['nombre' => 'Compras', 'icono' => 'bi-cart-check', 'link' => '../view/compras.php'],
     ['nombre' => 'Materiales', 'icono' => 'bi-graph-up', 'link' => '../view/materiales.php'], 
    /* ['nombre' => 'Nomina', 'icono' => 'bi-person-lines-fill', 'link' => '../view/nomina.php'], */
    ['nombre' => 'Personal', 'icono' => 'bi-person-workspace', 'link' => '../view/personal.php'],
    ['nombre' => 'Produccion', 'icono' => 'bi-gear', 'link' => '../view/produccion.php'],
    ['nombre' => 'Proveedores', 'icono' => 'bi-truck', 'link' => '../view/proveedores.php']
    /* ['nombre' => 'Ventas', 'icono' => 'bi-cash-stack', 'link' => '../view/ventas.php'] */
];

$current_page = basename($_SERVER['PHP_SELF']);

$colorSeccion = [
    'PaginaPrincipal.php' => 'linear-gradient(90deg, #396ac0ff, #5da3e0ff)',
    'articulos.php'        => 'linear-gradient(90deg, #1b4a9aff, #476ebcff)',
    'categoria.php'          => 'linear-gradient(90deg, #0064efff, #187498ff)',
    'clientes.php'         => 'linear-gradient(90deg, #15a9c0ff, #427bf5ff)',
    'compras.php'          => 'linear-gradient(90deg, #2e367dff, #8188c7ff)',
    'materiales.php'         => 'linear-gradient(90deg, #2581f9ff, #42a8bdff)', 
    /* 'nomina.php'           => 'linear-gradient(90deg, #2865c6ff, #50c2efff)', */
    'personal.php'         => 'linear-gradient(90deg, #41536dff, #3e6ac8ff)',
    'produccion.php'       => 'linear-gradient(90deg, #575da9ff, #5c91c0ff)',
    'proveedores.php'      => 'linear-gradient(90deg, #003369ff, #2675a6ff)'
    /* 'ventas.php'           => 'linear-gradient(90deg, #2e577dff, #819ec7ff)' */
];
?>

<div class="sidebar">
    <ul class="nav flex-column">
        <?php foreach ($menu as $item): 
            $pageName = basename($item['link']);
            $isActive = ($pageName == $current_page);
            $bgColor = $isActive ? "background: {$colorSeccion[$pageName]}; color: white; font-weight:600; box-shadow: inset 3px 0 0 #1b5e20;" : "";
        ?>
            <li class="nav-item">
                <a href="<?= $item['link'] ?>" class="nav-link d-flex align-items-center <?= $isActive ? 'active' : '' ?>" style="<?= $bgColor ?>">
                    <i class="bi <?= $item['icono'] ?> me-2" style="<?= $isActive ? 'color:white;' : '' ?>"></i> 
                    <?= $item['nombre'] ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
