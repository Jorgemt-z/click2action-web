<?php
/**
 * Sitemap XML dinámico: páginas fijas + entradas publicadas del blog.
 * Se sirve en /sitemap.xml (ver .htaccess).
 */
require __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');

$urls = [
    ['/', '1.0', 'weekly'],
    ['/servicios', '0.9', 'monthly'],
    ['/nosotros', '0.8', 'monthly'],
    ['/clientes', '0.8', 'monthly'],
    ['/blog', '0.8', 'weekly'],
    ['/contacto', '0.7', 'yearly'],
    ['/politica-de-datos', '0.2', 'yearly'],
];

$entries = [];
foreach ($urls as [$path, $priority, $freq]) {
    $entries[] = ['loc' => abs_url($path), 'priority' => $priority, 'changefreq' => $freq, 'lastmod' => null];
}

if ($pdo = db()) {
    $rows = $pdo->query(
        'SELECT slug, COALESCE(updated_at, published_at) AS lastmod FROM posts
         WHERE status = "published" AND published_at <= NOW() ORDER BY published_at DESC'
    )->fetchAll();
    foreach ($rows as $r) {
        $entries[] = ['loc' => abs_url('/blog/' . $r['slug']), 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => date('Y-m-d', strtotime($r['lastmod']))];
    }
    foreach (blog_categories() as $c) {
        if ($c['total']) {
            $entries[] = ['loc' => abs_url('/blog/categoria/' . $c['slug']), 'priority' => '0.4', 'changefreq' => 'weekly', 'lastmod' => null];
        }
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($entries as $u) {
    echo "  <url>\n    <loc>" . e($u['loc']) . "</loc>\n";
    if ($u['lastmod']) {
        echo '    <lastmod>' . $u['lastmod'] . "</lastmod>\n";
    }
    echo '    <changefreq>' . $u['changefreq'] . "</changefreq>\n    <priority>" . $u['priority'] . "</priority>\n  </url>\n";
}
echo '</urlset>';
