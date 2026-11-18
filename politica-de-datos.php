<?php
require __DIR__ . '/includes/bootstrap.php';

$page = [
    'id'          => 'politica',
    'title'       => 'Política de tratamiento de datos personales | Click 2 Action',
    'description' => 'Política de tratamiento de datos personales de Click 2 Action Comunicaciones Estratégicas, conforme a la Ley 1581 de 2012.',
    'path'        => '/politica-de-datos',
    'breadcrumbs' => [['Inicio', '/'], ['Política de datos', '/politica-de-datos']],
];
$hero = ['title' => 'Política de tratamiento de datos personales'];

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/partials/page-hero.php';
?>
<section class="section">
    <div class="container">
        <div class="prose">
            <p><em>Texto base para revisión del equipo jurídico antes de publicar.</em></p>

            <h2>1. Responsable del tratamiento</h2>
            <p><?= e(SITE_LEGAL_NAME) ?>, con domicilio en <?= e(ADDRESS_STREET) ?>, <?= e(ADDRESS_CITY) ?>, <?= e(ADDRESS_REGION) ?>, Colombia. Correo: <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a>. Teléfono: <?= e(PHONE_DISPLAY) ?>.</p>

            <h2>2. Datos que recolectamos</h2>
            <p>A través del formulario de contacto y de WhatsApp recolectamos nombre, empresa, correo electrónico, número de celular y la información que decidas compartir sobre tu proyecto.</p>

            <h2>3. Finalidad</h2>
            <ul>
                <li>Responder tu solicitud y enviarte propuestas comerciales.</li>
                <li>Hacer seguimiento a la relación comercial y a los servicios contratados.</li>
                <li>Enviar información sobre nuestros servicios, cuando lo hayas autorizado.</li>
            </ul>

            <h2>4. Derechos del titular</h2>
            <p>Como titular puedes conocer, actualizar, rectificar y suprimir tus datos, revocar la autorización y presentar quejas ante la Superintendencia de Industria y Comercio, de acuerdo con la Ley 1581 de 2012 y el Decreto 1377 de 2013.</p>

            <h2>5. Cómo ejercer tus derechos</h2>
            <p>Escríbenos a <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a> indicando tu nombre, documento y la solicitud. Responderemos dentro de los plazos establecidos por la ley.</p>

            <h2>6. Seguridad</h2>
            <p>Aplicamos medidas técnicas y administrativas razonables para proteger tu información contra acceso no autorizado, pérdida o alteración.</p>

            <h2>7. Vigencia</h2>
            <p>Esta política rige desde su publicación. Los datos se conservarán mientras sean necesarios para las finalidades descritas.</p>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
