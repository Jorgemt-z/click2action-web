<?php
/**
 * <head> con SEO técnico completo.
 * Cada página define $page antes de incluir este archivo:
 *   $page = [
 *     'id' => 'inicio', 'title' => '...', 'description' => '...', 'path' => '/',
 *     'image' => null, 'type' => 'website', 'robots' => 'index,follow',
 *     'breadcrumbs' => [['Inicio', '/'], ['Blog', '/blog']], 'schema' => [ ...JSON-LD extra ]
 *   ];
 */

$page = array_merge([
    'id'          => '',
    'title'       => SITE_NAME . ' | ' . SITE_TAGLINE,
    'description' => 'Agencia de marketing digital y diseño web en Medellín con más de ' . years_experience() . ' años de experiencia. Páginas web, SEO, pauta en Google y Meta, redes sociales y branding.',
    'path'        => '/',
    'image'       => null,
    'type'        => 'website',
    'robots'      => 'index,follow,max-image-preview:large',
    'breadcrumbs' => [],
    'schema'      => [],
    'body_class'  => '',
], $page ?? []);

$canonical = abs_url($page['path']);
$ogImage   = $page['image'] ?: abs_url('assets/img/og-click2action.jpg');

// --- Datos estructurados (JSON-LD) ---
$orgId = abs_url('/#organizacion');
$graph = [
    [
        '@type'       => ['Organization', 'ProfessionalService'],
        '@id'         => $orgId,
        'name'        => SITE_NAME,
        'legalName'   => SITE_LEGAL_NAME,
        'url'         => abs_url('/'),
        'logo'        => abs_url('assets/img/logo-click2action.svg'),
        'image'       => $ogImage,
        'description' => 'Agencia de marketing digital, diseño web, branding y comunicación audiovisual.',
        'foundingDate'=> (string) SITE_FOUNDED,
        'telephone'   => PHONE_LINK,
        'email'       => CONTACT_EMAIL,
        'priceRange'  => '$$',
        'address'     => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => ADDRESS_STREET,
            'addressLocality' => ADDRESS_CITY,
            'addressRegion'   => ADDRESS_REGION,
            'addressCountry'  => ADDRESS_COUNTRY,
        ],
        'geo'         => ['@type' => 'GeoCoordinates', 'latitude' => GEO_LAT, 'longitude' => GEO_LNG],
        'areaServed'  => ['@type' => 'Country', 'name' => 'Colombia'],
        'sameAs'      => array_values(SOCIAL),
        'contactPoint'=> [
            '@type'             => 'ContactPoint',
            'telephone'         => '+' . WHATSAPP_NUMBER,
            'contactType'       => 'sales',
            'availableLanguage' => 'Spanish',
        ],
    ],
    [
        '@type'     => 'WebSite',
        '@id'       => abs_url('/#sitio'),
        'url'       => abs_url('/'),
        'name'      => SITE_NAME,
        'inLanguage'=> 'es-CO',
        'publisher' => ['@id' => $orgId],
    ],
];

if ($page['breadcrumbs']) {
    $items = [];
    foreach ($page['breadcrumbs'] as $i => [$name, $path]) {
        $items[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $name, 'item' => abs_url($path)];
    }
    $graph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => $items];
}
foreach ($page['schema'] as $node) {
    $graph[] = $node;
}
$jsonLd = json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
?>
<!DOCTYPE html>
<html lang="es-CO">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page['title']) ?></title>
    <meta name="description" content="<?= e($page['description']) ?>">
    <meta name="robots" content="<?= e($page['robots']) ?>">
    <link rel="canonical" href="<?= e($canonical) ?>">
    <link rel="alternate" hreflang="es-CO" href="<?= e($canonical) ?>">

    <!-- Open Graph / redes sociales -->
    <meta property="og:locale" content="es_CO">
    <meta property="og:type" content="<?= e($page['type']) ?>">
    <meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
    <meta property="og:title" content="<?= e($page['title']) ?>">
    <meta property="og:description" content="<?= e($page['description']) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($page['title']) ?>">
    <meta name="twitter:description" content="<?= e($page['description']) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">

    <script>document.documentElement.classList.add('js');</script>
    <meta name="theme-color" content="#1b1f4f">
    <link rel="icon" href="<?= e(url('assets/img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="<?= e(url('assets/img/logo-redes.jpg')) ?>">

    <!-- Tipografías -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500..800&family=Manrope:wght@400..700&display=swap">

    <link rel="stylesheet" href="<?= e(asset('css/styles.css')) ?>">
    <script src="<?= e(asset('js/script.js')) ?>" defer></script>

    <script type="application/ld+json"><?= $jsonLd ?></script>

<?php if (GA4_ID): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e(GA4_ID) ?>"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e(GA4_ID) ?>');</script>
<?php endif; ?>
<?php if (defined('HOTJAR_ID') && (int) HOTJAR_ID > 0): ?>
    <script>(function(h,o,t,j,a,r){h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};h._hjSettings={hjid:<?= (int) HOTJAR_ID ?>,hjsv:6};a=o.getElementsByTagName('head')[0];r=o.createElement('script');r.async=1;r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;a.appendChild(r);})(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');</script>
<?php endif; ?>
</head>
<body class="<?= e($page['body_class']) ?>">
