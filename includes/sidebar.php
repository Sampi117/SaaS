<?php
$menu = [
    ['nombre' => 'Página principal', 'icono' => 'bi-house-door', 'link' => '../view/PaginaPrincipal.php'],
    ['nombre' => 'Ficha Tecnica', 'icono' => 'bi bi-file-earmark-text', 'link' => '../view/fichaTecnica.php'],
    ['nombre' => 'Procesos y Operaciones', 'icono' => 'bi bi-gear', 'link' => '../view/procesos.php'], 
    ['nombre' => 'Categorias', 'icono' => 'bi-wallet2', 'link' => '../view/categoria.php'], 
    ['nombre' => 'Clientes', 'icono' => 'bi-people', 'link' => '../view/clientes.php'],
    ['nombre' => 'Ingreso pedidos', 'icono' => 'bi-cart-check', 'link' => '../view/ingresoPedidos.php'],
    ['nombre' => 'Materiales', 'icono' => 'bi-graph-up', 'link' => '../view/materiales.php'], 
    ['nombre' => 'Produccion Pedidos', 'icono' => 'bi-person-lines-fill', 'link' => '../view/produccionPedidos.php'],
    ['nombre' => 'Personal', 'icono' => 'bi-person-workspace', 'link' => '../view/personal.php'],
    ['nombre' => 'Tareas', 'icono' => 'bi-gear', 'link' => '../view/tareas.php'],
    ['nombre' => 'Proveedores', 'icono' => 'bi-truck', 'link' => '../view/proveedores.php']
    /* ['nombre' => 'Ventas', 'icono' => 'bi-cash-stack', 'link' => '../view/ventas.php'] */
];

$current_page = basename($_SERVER['PHP_SELF']);

$colorSeccion = [
    'PaginaPrincipal.php' => '#F9F7F3',
    'fichaTecnica.php'    => '#F5F0E1',
    'procesos.php'        => '#F5ECE0',
    'categoria.php'       => '#F5EEE6',
    'clientes.php'        => '#F5F0E1',
    'compras.php'         => '#F5ECE0',
    'ingresoPedidos.php'  => '#F5F0E1',
    'materiales.php'      => '#F5EEE6',
    'personal.php'        => '#F5F0E1',
    'produccionPedidos.php'      => '#F5ECE0',
    'proveedores.php'     => '#F5EEE6'
    /* 'ventas.php'       => '#F5F0E1' */
];
?>

<div class="sidebar">
    <ul class="nav flex-column">
        <?php foreach ($menu as $item): 
            $pageName = basename($item['link']);
            $isActive = ($pageName == $current_page);
            $bgColor = $isActive ? "background: {$colorSeccion[$pageName]}; color: var(--primary-dark); font-weight:600; border-left: 4px solid var(--primary);" : "";
        ?>
            <li class="nav-item">
                <a href="<?= $item['link'] ?>" class="nav-link d-flex align-items-center <?= $isActive ? 'active' : '' ?>" style="<?= $bgColor ?>">
                    <i class="bi <?= $item['icono'] ?> me-2" style="<?= $isActive ? 'color: var(--primary-dark);' : 'color: var(--accent);' ?>"></i> 
                    <?= $item['nombre'] ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
