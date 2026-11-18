<?php
require __DIR__ . '/includes/bootstrap.php';

$pageNum  = max(1, (int) ($_GET['pagina'] ?? 1));
$catSlug  = isset($_GET['categoria']) ? preg_replace('/[^a-z0-9-]/', '', (string) $_GET['categoria']) : null;
$search   = isset($_GET['q']) ? mb_substr(trim((string) $_GET['q']), 0, 80) : null;
$search   = $search === '' ? null : $search;

$categories = blog_categories();
$result     = blog_posts($pageNum, $catSlug ?: null, $search);
$posts      = $result['items'];

$currentCat = null;
foreach ($categories as $c) {
    if ($c['slug'] === $catSlug) {
        $currentCat = $c;
    }
}
if ($catSlug && !$currentCat && db()) {
    include __DIR__ . '/404.php';
    exit;
}

// Construye la URL de listado conservando filtros
function blog_url(?string $cat, int $p = 1): string
{
    $path = $cat ? '/blog/categoria/' . $cat : '/blog';
    return $p > 1 ? $path . '?pagina=' . $p : $path;
}

$path = blog_url($catSlug, $pageNum);
$titleBase = $currentCat ? $currentCat['name'] . ' | Blog' : 'Blog de marketing digital y diseño web';
$page = [
    'id'          => 'blog',
    'title'       => $titleBase . ($pageNum > 1 ? " (página $pageNum)" : '') . ' | Click 2 Action',
    'description' => $currentCat
        ? 'Artículos sobre ' . mb_strtolower($currentCat['name']) . ' del equipo de Click 2 Action: guías prácticas para hacer crecer tu marca.'
        : 'Guías prácticas sobre páginas web, SEO, pauta en Google y Meta, redes sociales y branding, escritas por el equipo de Click 2 Action.',
    'path'        => $path,
    'robots'      => $search ? 'noindex,follow' : 'index,follow,max-image-preview:large',
    'breadcrumbs' => array_values(array_filter([
        ['Inicio', '/'],
        ['Blog', '/blog'],
        $currentCat ? [$currentCat['name'], blog_url($catSlug)] : null,
    ])),
    'schema'      => [[
        '@type'     => 'Blog',
        'name'      => 'Blog de Click 2 Action',
        'url'       => abs_url('/blog'),
        'publisher' => ['@id' => abs_url('/#organizacion')],
        'inLanguage'=> 'es-CO',
    ]],
];
$hero = [
    'title' => $currentCat ? $currentCat['name'] : 'Ideas que hacen click.',
    'lead'  => $currentCat
        ? 'Artículos de nuestro equipo sobre ' . mb_strtolower($currentCat['name']) . '.'
        : 'Guías prácticas sobre web, SEO, pauta y redes sociales, escritas por el equipo que las aplica todos los días.',
];

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/partials/page-hero.php';
?>

<section class="section section--tight">
    <div class="container">
        <div class="blog-toolbar">
            <?php if ($categories): ?>
                <nav aria-label="Categorías del blog">
                    <ul class="cat-filter">
                        <li><a href="<?= e(url('/blog')) ?>"<?= !$catSlug ? ' aria-current="page"' : '' ?>>Todo</a></li>
                        <?php foreach ($categories as $c): if (!$c['total']) continue; ?>
                            <li><a href="<?= e(url(blog_url($c['slug']))) ?>"<?= $c['slug'] === $catSlug ? ' aria-current="page"' : '' ?>><?= e($c['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            <form class="search-form" role="search" action="<?= e(url('/blog')) ?>" method="get">
                <label class="visually-hidden" for="q">Buscar en el blog</label>
                <?= icon('search') ?>
                <input type="search" id="q" name="q" placeholder="Buscar artículos" value="<?= e($search) ?>">
            </form>
        </div>

        <?php if ($search): ?>
            <p class="lead"><?= $result['total'] ?> resultado<?= $result['total'] === 1 ? '' : 's' ?> para «<?= e($search) ?>». <a href="<?= e(url('/blog')) ?>">Ver todos</a></p>
        <?php endif; ?>

        <?php if ($posts): ?>
            <div class="post-grid">
                <?php foreach ($posts as $i => $post):
                    $featured = ($i === 0 && $pageNum === 1 && !$catSlug && !$search);
                    include __DIR__ . '/includes/partials/post-card.php';
                endforeach; $featured = false; ?>
            </div>

            <?php if ($result['pages'] > 1): ?>
                <nav aria-label="Paginación del blog">
                    <ul class="pagination">
                        <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
                            <li>
                                <?php if ($p === $pageNum): ?>
                                    <span aria-current="page"><?= $p ?></span>
                                <?php else: ?>
                                    <a href="<?= e(url(blog_url($catSlug, $p)) . ($search ? (str_contains(blog_url($catSlug, $p), '?') ? '&' : '?') . 'q=' . rawurlencode($search) : '')) ?>" aria-label="Página <?= $p ?>"><?= $p ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <h2><?= $search ? 'No encontramos artículos con esa búsqueda' : 'Pronto publicaremos nuevos artículos' ?></h2>
                <p><?= $search ? 'Prueba con otra palabra, como «SEO», «redes» o «página web».' : 'Mientras tanto, cuéntanos qué te gustaría leer o qué necesita tu marca.' ?></p>
                <a class="btn btn--wa" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> Escríbenos</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
