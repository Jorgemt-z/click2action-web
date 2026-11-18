<?php
require __DIR__ . '/includes/bootstrap.php';

$page = [
    'id'          => 'servicios',
    'title'       => 'Servicios de diseño web, marketing digital y branding | Click 2 Action',
    'description' => 'Desarrollo web, landing pages y ecommerce; pauta en Google Ads y Meta; SEO; redes sociales; branding, producción audiovisual y gestión de bases de datos. Cotiza por WhatsApp con Click 2 Action.',
    'path'        => '/servicios',
    'breadcrumbs' => [['Inicio', '/'], ['Servicios', '/servicios']],
];

// Datos estructurados: un Service por cada línea
foreach ($SERVICES as $s) {
    $page['schema'][] = [
        '@type'       => 'Service',
        'name'        => $s['name'],
        'description' => $s['text'],
        'provider'    => ['@id' => abs_url('/#organizacion')],
        'areaServed'  => 'Colombia',
        'url'         => abs_url('/servicios#' . $s['slug']),
        'hasOfferCatalog' => [
            '@type' => 'OfferCatalog',
            'name'  => $s['name'],
            'itemListElement' => array_map(fn($i) => ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => $i]], $s['items']),
        ],
    ];
}

$hero = [
    'title' => 'Soluciones digitales para que tu marca destaque.',
    'lead'  => 'Cinco áreas que trabajan con una misma estrategia. Contrata una o todas: siempre tendrás un equipo que conoce tu negocio.',
];

$essentials = [
    ['Contenido relevante',     'Textos e imágenes que responden lo que tu cliente busca.'],
    ['Navegación intuitiva',    'Cada página lleva a una acción clara: escribir, llamar o comprar.'],
    ['SEO desde el inicio',     'Estructura, velocidad y etiquetas pensadas para Google.'],
    ['Diseño responsive',       'Se ve y funciona perfecto en celular, tablet y computador.'],
    ['Seguridad',               'Certificado SSL, formularios protegidos y actualizaciones al día.'],
];

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/partials/page-hero.php';
?>

<section class="section">
    <div class="container">
        <?php foreach ($SERVICES as $s): ?>
            <article class="service-detail" id="<?= e($s['slug']) ?>">
                <div class="service-detail__intro reveal">
                    <h2><?= e($s['name']) ?></h2>
                    <p><?= e($s['text']) ?></p>
                    <a class="btn btn--wa" href="<?= e(wa_link($s['wa'])) ?>" target="_blank" rel="noopener" data-track="whatsapp-servicio-<?= e($s['slug']) ?>">
                        <?= icon('whatsapp') ?> Cotizar <?= e(mb_strtolower($s['name'])) ?>
                    </a>
                </div>
                <ul class="checklist reveal" aria-label="Incluye">
                    <?php foreach ($s['items'] as $item): ?>
                        <li><?= icon('check') ?><?= e($item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section section--dark" aria-labelledby="essentials-title">
    <div class="container">
        <div class="section-head">
            <h2 id="essentials-title">Los 5 infaltables de una página web profesional</h2>
            <p>Todo sitio que sale de Click 2 Action cumple con estos cinco puntos antes de publicarse.</p>
        </div>
        <ul class="essentials">
            <?php foreach ($essentials as [$title, $text]): ?>
                <li class="reveal"><h3><?= e($title) ?></h3><p><?= e($text) ?></p></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
