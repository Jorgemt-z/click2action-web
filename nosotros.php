<?php
require __DIR__ . '/includes/bootstrap.php';

$years = years_experience();
$page = [
    'id'          => 'nosotros',
    'title'       => 'Quiénes somos | Agencia de comunicaciones en Medellín | Click 2 Action',
    'description' => "Conoce a Click 2 Action: agencia de comunicaciones estratégicas y marketing digital con +{$years} años, " . team_size() . " especialistas y " . active_brands() . " marcas activas en Envigado, Antioquia.",
    'path'        => '/nosotros',
    'breadcrumbs' => [['Inicio', '/'], ['Nosotros', '/nosotros']],
    'schema'      => [[
        '@type' => 'AboutPage',
        'name'  => 'Quiénes somos',
        'url'   => abs_url('/nosotros'),
        'about' => ['@id' => abs_url('/#organizacion')],
    ]],
];
$hero = [
    'title' => 'Un aliado estratégico para impulsar el crecimiento de tu marca.',
    'lead'  => 'Somos una compañía de comunicaciones que integra marketing digital, publicidad, relaciones públicas y desarrollo web, con un equipo creativo, apasionado y capacitado.',
];

$team = site_team();

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/partials/page-hero.php';
?>

<section class="section" aria-labelledby="stats-title">
    <div class="container">
        <h2 id="stats-title" class="visually-hidden">Click 2 Action en cifras</h2>
        <ul class="stats">
            <?php foreach ([
                [$years, 'años haciendo que las marcas hagan click con sus clientes'],
                [active_brands(), 'marcas activas'],
                [team_size(), 'profesionales disponibles para hacer crecer tu marca'],
            ] as $i => [$num, $label]): ?>
                <li class="stat reveal" style="transition-delay: <?= $i * 120 ?>ms">
                    <span class="stat__num"><sup>+</sup><?= (int) $num ?></span>
                    <span class="stat__label"><?= e($label) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="section section--paper" aria-labelledby="method-title">
    <div class="container">
        <div class="section-head">
            <h2 id="method-title">Nuestro método</h2>
            <p>Planeamos, diseñamos, ejecutamos, analizamos y seguimos. Cinco pasos que se repiten cada mes para que tu marca mejore de forma constante.</p>
        </div>
        <ol class="process">
            <?php foreach ($PROCESS as $step): ?>
                <li class="process__step reveal">
                    <h3><?= e($step['name']) ?></h3>
                    <p><?= e($step['text']) ?></p>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>

<section class="section section--dark" aria-labelledby="team-title">
    <div class="container">
        <div class="section-head section-head--split<?= $team ? '' : ' section-head--solo' ?>">
            <h2 id="team-title">El equipo detrás de cada click</h2>
            <p>Somos <?= team_size() ?> expertos en ingeniería, comunicación, estrategia digital, pauta, diseño, desarrollo web, contenido y producción audiovisual, trabajando bajo un mismo techo. Cada marca tiene un equipo dedicado que conoce su negocio y responde por sus resultados, no un freelancer de turno.</p>
        </div>
    </div>
    <?php if ($team):
        // Con pocas personas se muestran fijas; desde 6 se mueven en carrusel continuo.
        $teamMarquee = count($team) >= 6; ?>
        <div class="team-reel<?= $teamMarquee ? ' marquee' : ' container' ?>"<?= $teamMarquee ? ' data-marquee style="--duration: ' . (count($team) * 4) . 's"' : '' ?>>
            <ul class="<?= $teamMarquee ? 'marquee__track' : 'team-static' ?>" aria-label="Integrantes del equipo">
                <?php foreach ($team as $member): ?>
                    <li class="team-card">
                        <img src="<?= e($member['photo']) ?>" alt="" loading="lazy" decoding="async" width="400" height="500">
                        <span class="team-card__name"><?= e($member['name']) ?></span>
                        <?php if ($member['role']): ?><span class="team-card__role"><?= e($member['role']) ?></span><?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
