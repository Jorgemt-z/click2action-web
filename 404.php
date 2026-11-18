<?php
require_once __DIR__ . '/includes/bootstrap.php';

http_response_code(404);
$page = [
    'id'          => '404',
    'title'       => 'Página no encontrada | Click 2 Action',
    'description' => 'La página que buscas no existe o cambió de dirección.',
    'path'        => '/404',
    'robots'      => 'noindex,follow',
];

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container big-years error-page">
        <p class="big-years__num" aria-hidden="true">404</p>
        <div class="big-years__text">
            <h1 style="font-size:var(--step-4)">Este click no llevó a ninguna parte.</h1>
            <p>La página que buscas no existe o cambió de dirección. Vuelve al inicio o cuéntanos qué estabas buscando.</p>
            <div class="hero__actions">
                <a class="btn btn--lg" href="<?= e(url('/')) ?>">Ir al inicio</a>
                <a class="btn btn--ghost btn--lg" href="<?= e(url('/blog')) ?>">Ver el blog</a>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
