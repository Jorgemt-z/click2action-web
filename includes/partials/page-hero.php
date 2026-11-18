<?php
/** Encabezado de páginas internas. Requiere $hero = ['title' => '', 'lead' => ''] y $page['breadcrumbs']. */
$crumbs = $page['breadcrumbs'] ?? [];
?>
<section class="page-hero<?= !empty($hero['class']) ? ' ' . e($hero['class']) : '' ?>">
    <div class="container">
        <?php if ($crumbs): ?>
            <nav class="breadcrumbs" aria-label="Ruta de navegación">
                <ol>
                    <?php foreach ($crumbs as $i => [$name, $path]): ?>
                        <?php if ($i === array_key_last($crumbs)): ?>
                            <li><span aria-current="page"><?= e($name) ?></span></li>
                        <?php else: ?>
                            <li><a href="<?= e(url($path)) ?>"><?= e($name) ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>
        <h1><?= $hero['title_html'] ?? e($hero['title']) ?></h1>
        <?php if (!empty($hero['lead'])): ?><p class="lead"><?= e($hero['lead']) ?></p><?php endif; ?>
        <?php if (!empty($hero['extra'])) echo $hero['extra']; ?>
    </div>
</section>
