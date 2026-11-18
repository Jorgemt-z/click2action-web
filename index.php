<?php
require __DIR__ . '/includes/bootstrap.php';

$years = years_experience();
$page = [
    'id'          => 'inicio',
    'title'       => 'Agencia de marketing digital y diseño web en Medellín | Click 2 Action',
    'description' => "Diseñamos páginas web y estrategias de marketing digital que convierten visitas en conversaciones y ventas. +{$years} años en Medellín y Envigado. Escríbenos por WhatsApp.",
    'path'        => '/',
];

$composerOptions = [
    ['Página web',        'Hola Click 2 Action, quiero cotizar una página web para mi empresa.'],
    ['Redes sociales', 'Hola Click 2 Action, quiero mejorar los resultados de mis redes sociales.'],
    ['Bases de datos',    'Hola Click 2 Action, quisiera que me ayuden a gestionar mis bases de datos.'],
    ['Pauta en Google y Meta', 'Hola Click 2 Action, quiero hacer campañas en Google y redes sociales.'],
    ['Branding',          'Hola Click 2 Action, quiero crear o renovar la identidad de mi marca.'],
    ['Foto y video',      'Hola Click 2 Action, necesito producción de foto o video para mi marca.'],
];


include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
?>

<section class="hero" aria-labelledby="hero-title">
    <svg class="hero__art draw" viewBox="2 2 105 103" aria-hidden="true" focusable="false">
        <g fill="none" stroke="currentColor" stroke-linejoin="round" stroke-linecap="round">
            <path d="M8 81V40a32 32 0 1 1 16.4 27.6Z"/>
            <path d="M101 99V58a32 32 0 1 0-16.4 27.6Z"/>
        </g>
    </svg>

    <div class="container hero__inner">
        <div class="hero__copy">
            <h1 id="hero-title" class="hero__title">Cada click cuenta. <em>Cada resultado habla.</em></h1>
            <p class="hero__lead">
                Somos una compañía especializada en <strong>soluciones estratégicas, digitales y tecnológicas.</strong>
                Desde hace más de <?= $years ?> años impulsamos el crecimiento de las marcas, conectando oportunidades
                con resultados y convirtiendo visitas en conversaciones y conversaciones en ventas.
            </p>
            <div class="hero__actions">
                <a class="btn btn--light btn--lg" href="<?= e(url('/servicios')) ?>">Ver servicios</a>
                <a class="btn btn--ghost-light btn--lg" href="<?= e(url('/clientes')) ?>">Nuestros clientes</a>
            </div>
        </div>

        <div class="composer" data-composer data-number="<?= e(WHATSAPP_NUMBER) ?>" aria-labelledby="composer-title">
            <div class="composer__bar">
                <span class="composer__avatar"><?php include __DIR__ . '/includes/logo.php'; ?></span>
                <div class="composer__who">
                    <strong id="composer-title">Click 2 Action</strong>
                    <span>Responderemos lo antes posible</span>
                </div>
            </div>
            <div class="composer__body">
                <p class="bubble bubble--in">¡Hola! 👋 ¿En qué podemos ayudar a tu marca hoy?<time>Ahora</time></p>

                <p class="composer__label" id="composer-choose">Elige un tema y te dejamos el mensaje listo:</p>
                <div class="chips" role="group" aria-labelledby="composer-choose">
                    <?php foreach ($composerOptions as $i => [$label, $msg]): ?>
                        <button type="button" class="chip" data-msg="<?= e($msg) ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>"><?= e($label) ?></button>
                    <?php endforeach; ?>
                </div>

                <p class="bubble bubble--out composer__message-wrap" aria-live="polite">
                    <span data-composer-message><?= e($composerOptions[0][1]) ?></span>
                </p>

                <a class="btn btn--wa btn--lg composer__send" data-composer-send data-track="whatsapp-hero"
                   href="<?= e(wa_link($composerOptions[0][1])) ?>" target="_blank" rel="noopener">
                    <?= icon('whatsapp') ?> Enviar por WhatsApp
                </a>
                <p class="composer__note">Se abre WhatsApp con el mensaje. Puedes editarlo antes de enviarlo.</p>
            </div>
        </div>
    </div>
</section>

<section class="clients-strip" aria-labelledby="clients-strip-title">
    <div class="container">
        <h2 id="clients-strip-title" class="clients-strip__title"><?= active_brands() ?> marcas activas hacen click con nosotros</h2>
    </div>
    <div class="marquee" data-marquee>
        <ul class="marquee__track">
            <?php foreach ($CLIENTS as $client): $logo = $client['logo']; ?>
                <li class="client-logo">
                    <?php if ($logo): ?>
                        <img src="<?= e($logo) ?>" alt="<?= e($client['name']) ?>" loading="lazy" decoding="async" width="120" height="120">
                    <?php else: ?>
                        <span class="client-logo__name"><?= e($client['name']) ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="section" aria-labelledby="services-title">
    <div class="container">
        <div class="section-head section-head--split">
            <h2 id="services-title">Todo lo que tu marca necesita, en un solo equipo.</h2>
            <p>Diseño web, marketing, branding, producción audiovisual y gestión de datos trabajando con la misma estrategia y los mismos objetivos.</p>
        </div>
        <ul class="service-list">
            <?php foreach ($SERVICES as $s): ?>
                <li>
                    <a class="service-row" href="<?= e(url('/servicios#' . $s['slug'])) ?>">
                        <span class="service-row__name"><?= e($s['name']) ?></span>
                        <p class="service-row__text"><?= e($s['short']) ?></p>
                        <span class="service-row__go" aria-hidden="true"><?= icon('arrow') ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>



<?php include __DIR__ . '/includes/footer.php'; ?>
