<?php
/** Cierre de página: franja de contacto, pie y botón flotante de WhatsApp. */
$hideCta = $hideCta ?? false;
?>
<?php if (!$hideCta): ?>
<section class="cta-band" aria-labelledby="cta-title">
    <div class="container cta-band__inner">
        <h2 id="cta-title" class="cta-band__title">Hagamos click.</h2>
        <p class="cta-band__text">Cuéntanos qué necesita tu marca y te respondemos por WhatsApp el mismo día hábil.</p>
        <div class="cta-band__actions">
            <a class="btn btn--wa btn--lg" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener" data-track="whatsapp-cta">
                <?= icon('whatsapp') ?> Escribir por WhatsApp
            </a>
            <a class="btn btn--ghost-light btn--lg" href="<?= e(url('/contacto')) ?>">Prefiero el formulario</a>
        </div>
    </div>
</section>
<?php endif; ?>
</main>

<footer class="site-footer">
    <div class="container site-footer__grid">
        <div class="site-footer__brand">
            <a class="brand brand--light" href="<?= e(url('/')) ?>" aria-label="Click 2 Action, ir al inicio">
                <?php include __DIR__ . '/logo.php'; ?>
                <span class="brand__name">Click <b>2</b> Action</span>
            </a>
            <p>Cada click cuenta y cada resultado habla. Agencia de marketing digital y diseño web con más de <?= years_experience() ?> años acompañando marcas en Colombia.</p>
            <ul class="social" aria-label="Redes sociales">
                <?php foreach (SOCIAL as $network => $link): ?>
                    <li><a href="<?= e($link) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($network)) ?>"><?= icon($network) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <nav class="site-footer__col" aria-label="Mapa del sitio">
            <h2 class="site-footer__title">Agencia</h2>
            <ul>
                <li><a href="<?= e(url('/nosotros')) ?>">Nosotros</a></li>
                <li><a href="<?= e(url('/clientes')) ?>">Clientes</a></li>
                <li><a href="<?= e(url('/blog')) ?>">Blog</a></li>
                <li><a href="<?= e(url('/contacto')) ?>">Contacto</a></li>
            </ul>
        </nav>

        <nav class="site-footer__col" aria-label="Servicios">
            <h2 class="site-footer__title">Servicios</h2>
            <ul>
                <?php foreach ($SERVICES as $s): ?>
                    <li><a href="<?= e(url('/servicios#' . $s['slug'])) ?>"><?= e($s['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="site-footer__col">
            <h2 class="site-footer__title">Contacto</h2>
            <ul class="contact-list">
                <li><?= icon('whatsapp') ?><a href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">WhatsApp</a></li>
                <li><?= icon('phone') ?><a href="tel:<?= e(PHONE_LINK) ?>"><?= e(PHONE_DISPLAY) ?></a></li>
                <li><?= icon('mail') ?><a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a></li>
                <li><?= icon('pin') ?><span><?= e(ADDRESS_STREET) ?>, <?= e(ADDRESS_CITY) ?>, <?= e(ADDRESS_REGION) ?></span></li>
            </ul>
        </div>
    </div>

    <div class="container site-footer__legal">
        <p>© <?= date('Y') ?> <?= e(SITE_LEGAL_NAME) ?>. Todos los derechos reservados.</p>
        <a href="<?= e(url('/politica-de-datos')) ?>">Política de tratamiento de datos</a>
    </div>
</footer>

<a class="wa-float" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener" aria-label="Escríbenos por WhatsApp" data-track="whatsapp-float">
    <?= icon('whatsapp') ?>
    <span class="wa-float__label">¿Hablamos?</span>
</a>
</body>
</html>
