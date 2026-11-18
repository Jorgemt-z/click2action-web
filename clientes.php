<?php
require __DIR__ . '/includes/bootstrap.php';

$page = [
    'id'          => 'clientes',
    'title'       => 'Clientes | Marcas que confían en nosotros | Click 2 Action',
    'description' => "Surtipulpas, Serenn, Autoplus, Ditransa y " . active_brands() . " marcas activas confían en Click 2 Action para su marketing digital, diseño web y branding.",
    'path'        => '/clientes',
    'breadcrumbs' => [['Inicio', '/'], ['Clientes', '/clientes']],
    'schema'      => [[
        '@type' => 'CollectionPage',
        'name'  => 'Clientes de Click 2 Action',
        'url'   => abs_url('/clientes'),
    ]],
];
$hero = [
    'title' => 'Compañeros, clientes, amigos.',
    'lead'  => 'Marcas de salud, construcción, logística, alimentos, legal, político y comercio que crecen con nosotros.',
];

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/partials/page-hero.php';
?>

<section class="section" aria-labelledby="wall-title">
    <div class="container">
        <div class="section-head section-head--split">
            <h2 id="wall-title">Marcas que confían en nosotros</h2>
            <p>Hoy trabajamos con <?= active_brands() ?> marcas activas de salud, construcción, logística, alimentos, legal y comercio. Estas son algunas de ellas.</p>
        </div>
        <ul class="logo-wall">
            <?php foreach ($CLIENTS as $client): $logo = $client['logo']; ?>
                <li class="reveal">
                    <?php if ($logo): ?>
                        <img src="<?= e($logo) ?>" alt="<?= e($client['name']) ?>" loading="lazy" decoding="async" width="150" height="150">
                    <?php else: ?>
                        <span class="client-logo__name"><?= e($client['name']) ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
