<?php
require __DIR__ . '/includes/bootstrap.php';

$slug = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['slug'] ?? ''));
$post = $slug ? blog_post($slug) : null;

if (!$post) {
    include __DIR__ . '/404.php';
    exit;
}

$postPath = '/blog/' . $post['slug'];
$cover    = cover_url($post['cover_image']);
$content  = clean_html($post['content']);
$desc     = $post['meta_description'] ?: ($post['excerpt'] ?: excerpt($post['content'], 155));
$related  = blog_latest(3, (int) $post['id']);

$page = [
    'id'          => 'blog',
    'title'       => ($post['meta_title'] ?: $post['title']) . ' | Click 2 Action',
    'description' => $desc,
    'path'        => $postPath,
    'image'       => $cover ? (preg_match('#^https?://#', $cover) ? $cover : abs_url($cover)) : null,
    'type'        => 'article',
    'breadcrumbs' => array_values(array_filter([
        ['Inicio', '/'],
        ['Blog', '/blog'],
        $post['category'] ? [$post['category'], '/blog/categoria/' . $post['category_slug']] : null,
        [$post['title'], $postPath],
    ])),
    'schema'      => [[
        '@type'            => 'BlogPosting',
        'headline'         => $post['title'],
        'description'      => $desc,
        'image'            => $cover ? abs_url($cover) : abs_url('assets/img/og-click2action.jpg'),
        'datePublished'    => date(DATE_ATOM, strtotime($post['published_at'])),
        'dateModified'     => date(DATE_ATOM, strtotime($post['updated_at'] ?: $post['published_at'])),
        'author'           => ['@type' => 'Person', 'name' => $post['author'] ?: SITE_NAME],
        'publisher'        => ['@id' => abs_url('/#organizacion')],
        'mainEntityOfPage' => abs_url($postPath),
        'articleSection'   => $post['category'],
        'inLanguage'       => 'es-CO',
        'wordCount'        => str_word_count(strip_tags($post['content'])),
    ]],
];

$hero = [
    'title' => $post['title'],
    'class' => 'article-hero',
    'extra' => '<div class="post-meta">'
        . ($post['category'] ? '<a href="' . e(url('/blog/categoria/' . $post['category_slug'])) . '">' . e($post['category']) . '</a>' : '')
        . '<time datetime="' . e(date('Y-m-d', strtotime($post['published_at']))) . '">' . e(date_es($post['published_at'])) . '</time>'
        . '<span>' . reading_time($post['content']) . ' min de lectura</span>'
        . ($post['author'] ? '<span>Por ' . e($post['author']) . '</span>' : '')
        . '</div>',
];

$shareUrl = rawurlencode(abs_url($postPath));
$shareTxt = rawurlencode($post['title']);

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/partials/page-hero.php';
?>

<?php if ($cover): ?>
<div class="container article-cover">
    <img src="<?= e($cover) ?>" alt="<?= e($post['cover_alt'] ?: $post['title']) ?>" width="1200" height="600" fetchpriority="high">
</div>
<?php endif; ?>

<section class="section">
    <div class="container article-layout">
        <article class="prose">
            <?= $content ?>

            <hr>
            <div class="share">
                <span>Compartir:</span>
                <a href="https://wa.me/?text=<?= $shareTxt ?>%20<?= $shareUrl ?>" target="_blank" rel="noopener" aria-label="Compartir por WhatsApp"><?= icon('whatsapp') ?></a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $shareUrl ?>" target="_blank" rel="noopener" aria-label="Compartir en LinkedIn"><?= icon('linkedin') ?></a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" rel="noopener" aria-label="Compartir en Facebook"><?= icon('facebook') ?></a>
                <a href="#" data-copy-link aria-label="Copiar enlace" title="Copiar enlace"><?= icon('arrow-up') ?></a>
            </div>
        </article>

        <aside class="article-aside">
            <div class="aside-card">
                <h2>¿Quieres aplicarlo en tu marca?</h2>
                <p>Te ayudamos a convertir estas ideas en resultados. Escríbenos y lo hablamos.</p>
                <a class="btn btn--wa" href="<?= e(wa_link('Hola Click 2 Action, leí el artículo "' . $post['title'] . '" y quiero hablar sobre mi marca.')) ?>" target="_blank" rel="noopener" data-track="whatsapp-articulo">
                    <?= icon('whatsapp') ?> Hablemos
                </a>
            </div>
        </aside>
    </div>
</section>

<?php if ($related): ?>
<section class="section section--paper" aria-labelledby="related-title">
    <div class="container">
        <div class="section-head">
            <h2 id="related-title">Sigue leyendo</h2>
        </div>
        <div class="post-grid">
            <?php foreach ($related as $post): include __DIR__ . '/includes/partials/post-card.php'; endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
