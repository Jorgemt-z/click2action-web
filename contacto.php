<?php
require __DIR__ . '/includes/bootstrap.php';

$page = [
    'id'          => 'contacto',
    'title'       => 'Contacto | Escríbenos por WhatsApp | Click 2 Action',
    'description' => 'Habla con Click 2 Action por WhatsApp o déjanos tus datos en el formulario. Estamos en Envigado, Antioquia, y trabajamos con marcas de toda Colombia.',
    'path'        => '/contacto',
    'breadcrumbs' => [['Inicio', '/'], ['Contacto', '/contacto']],
    'schema'      => [[
        '@type' => 'ContactPage',
        'name'  => 'Contacto',
        'url'   => abs_url('/contacto'),
    ]],
];
$hideCta = true;

// Mensaje del envío sin JavaScript (redirección desde enviar.php)
$flash = $_SESSION['contact_flash'] ?? null;
unset($_SESSION['contact_flash']);
$old = $flash['old'] ?? [];
$errors = $flash['errors'] ?? [];

$motivos = array_merge(array_column($SERVICES, 'name'), ['Cotización de un proyecto completo', 'Otro']);

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container contact-grid">
        <div class="wa-panel reveal">
            <h1 id="wa-title">Escríbenos por WhatsApp</h1>
            <p>Te respondemos el mismo día hábil. Cuéntanos qué necesitas y en qué ciudad está tu negocio.</p>
            <a class="btn btn--wa btn--lg" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener" data-track="whatsapp-contacto">
                <?= icon('whatsapp') ?> Abrir WhatsApp
            </a>
            <ul class="contact-list">
                <li><?= icon('phone') ?><a href="tel:<?= e(PHONE_LINK) ?>"><?= e(PHONE_DISPLAY) ?></a></li>
                <li><?= icon('mail') ?><a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a></li>
                <li><?= icon('pin') ?><span><?= e(ADDRESS_STREET) ?>, <?= e(ADDRESS_CITY) ?>, <?= e(ADDRESS_REGION) ?></span></li>
            </ul>
            <div class="map-embed">
                <iframe title="Ubicación de Click 2 Action en Envigado"
                        src="https://www.google.com/maps?q=<?= rawurlencode(ADDRESS_STREET . ', ' . ADDRESS_CITY . ', ' . ADDRESS_REGION . ', Colombia') ?>&output=embed"
                        loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>

        <div class="reveal">
            <h2 style="font-size:var(--step-3)">Prefiero el formulario</h2>
            <p class="lead" style="margin-bottom:var(--space-l)">Completa tus datos y un asesor te contacta en menos de 24 horas hábiles.</p>

            <form class="form" action="<?= e(url('/enviar.php')) ?>" method="post" novalidate data-contact-form>
                <?= csrf_field() ?>
                <div class="hp-field" aria-hidden="true">
                    <label for="website">No llenar este campo</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="form__row form__row--2">
                    <div class="field<?= isset($errors['nombre']) ? ' has-error' : '' ?>">
                        <label for="nombre">Nombre completo</label>
                        <input id="nombre" name="nombre" type="text" required minlength="3" maxlength="120" autocomplete="name" value="<?= e($old['nombre'] ?? '') ?>" aria-describedby="err-nombre">
                        <p class="field__error" id="err-nombre" data-error-for="nombre"><?= e($errors['nombre'] ?? '') ?></p>
                    </div>
                    <div class="field">
                        <label for="empresa">Empresa <small>(opcional)</small></label>
                        <input id="empresa" name="empresa" type="text" maxlength="120" autocomplete="organization" value="<?= e($old['empresa'] ?? '') ?>">
                    </div>
                </div>

                <div class="form__row form__row--2">
                    <div class="field<?= isset($errors['email']) ? ' has-error' : '' ?>">
                        <label for="email">Correo electrónico</label>
                        <input id="email" name="email" type="email" required maxlength="160" autocomplete="email" inputmode="email" value="<?= e($old['email'] ?? '') ?>" aria-describedby="err-email">
                        <p class="field__error" id="err-email" data-error-for="email"><?= e($errors['email'] ?? '') ?></p>
                    </div>
                    <div class="field<?= isset($errors['telefono']) ? ' has-error' : '' ?>">
                        <label for="telefono">Celular</label>
                        <input id="telefono" name="telefono" type="tel" required maxlength="20" autocomplete="tel" inputmode="tel" placeholder="300 123 4567" value="<?= e($old['telefono'] ?? '') ?>" aria-describedby="err-telefono">
                        <p class="field__error" id="err-telefono" data-error-for="telefono"><?= e($errors['telefono'] ?? '') ?></p>
                    </div>
                </div>

                <div class="field<?= isset($errors['motivo']) ? ' has-error' : '' ?>">
                    <label for="motivo">¿En qué te podemos ayudar?</label>
                    <select id="motivo" name="motivo" required aria-describedby="err-motivo">
                        <option value="">Elige una opción</option>
                        <?php foreach ($motivos as $m): ?>
                            <option value="<?= e($m) ?>"<?= ($old['motivo'] ?? '') === $m ? ' selected' : '' ?>><?= e($m) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="field__error" id="err-motivo" data-error-for="motivo"><?= e($errors['motivo'] ?? '') ?></p>
                </div>

                <div class="field<?= isset($errors['mensaje']) ? ' has-error' : '' ?>">
                    <label for="mensaje">Cuéntanos sobre tu proyecto</label>
                    <textarea id="mensaje" name="mensaje" required minlength="10" maxlength="3000" placeholder="Qué vende tu empresa, qué quieres lograr y para cuándo." aria-describedby="err-mensaje"><?= e($old['mensaje'] ?? '') ?></textarea>
                    <p class="field__error" id="err-mensaje" data-error-for="mensaje"><?= e($errors['mensaje'] ?? '') ?></p>
                </div>

                <div>
                    <label class="check<?= isset($errors['acepto']) ? ' has-error' : '' ?>">
                        <input type="checkbox" name="acepto" value="1" required aria-describedby="err-acepto">
                        <span>Autorizo el tratamiento de mis datos personales según la <a href="<?= e(url('/politica-de-datos')) ?>" target="_blank">política de tratamiento de datos</a> (Ley 1581 de 2012).</span>
                    </label>
                    <p class="field__error" id="err-acepto" data-error-for="acepto"><?= e($errors['acepto'] ?? '') ?></p>
                </div>

                <div class="alert<?= $flash ? ($flash['ok'] ? ' alert--success' : ' alert--error') : '' ?>" role="status" tabindex="-1" data-form-status><?= e($flash['message'] ?? '') ?></div>

                <div>
                    <button class="btn btn--lg" type="submit">Enviar mensaje <?= icon('arrow') ?></button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
