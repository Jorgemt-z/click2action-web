<?php
/** Cabecera y navegación principal. Requiere $page['id']. */
$current = $page['id'] ?? '';
$menu = [
    'inicio'    => ['Inicio', '/'],
    'nosotros'  => ['Nosotros', '/nosotros'],
    'servicios' => ['Servicios', '/servicios'],
    'clientes'  => ['Clientes', '/clientes'],
    'blog'      => ['Blog', '/blog'],
    'contacto'  => ['Contacto', '/contacto'],
];
include __DIR__ . '/icons.php';
?>
<a class="skip-link" href="#contenido">Saltar al contenido</a>

<header class="site-header" data-header>
    <div class="container site-header__inner">
        <a class="brand" href="<?= e(url('/')) ?>" aria-label="Click 2 Action, ir al inicio">
            <?php include __DIR__ . '/logo.php'; ?>
            <span class="brand__name">Click <b>2</b> Action</span>
        </a>

        <nav class="nav" id="menu-principal" aria-label="Menú principal" data-nav>
            <ul class="nav__list">
                <?php foreach ($menu as $id => [$label, $path]): ?>
                    <li><a class="nav__link" href="<?= e(url($path)) ?>"<?= nav_active($id, $current) ?>><?= e($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
            <a class="btn btn--wa nav__cta" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener" data-track="whatsapp-header">
                <?= icon('whatsapp') ?> Escríbenos
            </a>
        </nav>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu-principal" data-nav-toggle>
            <span class="visually-hidden">Abrir menú</span>
            <?= icon('menu', 'icon nav-toggle__open') ?>
            <?= icon('close', 'icon nav-toggle__close') ?>
        </button>
    </div>
</header>

<main id="contenido">
