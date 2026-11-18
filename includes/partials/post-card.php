<?php
/** Tarjeta de entrada del blog. Requiere $post; opcional $featured. */
$postUrl  = url('/blog/' . $post['slug']);
$cover    = cover_url($post['cover_image'] ?? null);
$isFeat   = !empty($featured);
$headTag  = $isFeat ? 'h2' : 'h3';
?>
<article class="post-card<?= $isFeat ? ' post-card--featured' : '' ?> reveal">
    <a class="post-card__media" href="<?= e($postUrl) ?>" tabindex="-1" aria-hidden="true">
        <?php if ($cover): ?>
            <img src="<?= e($cover) ?>" alt="" loading="lazy" decoding="async" width="800" height="500">
        <?php else: ?>
            <span class="post-card__placeholder"><?php include __DIR__ . '/../logo.php'; ?></span>
        <?php endif; ?>
    </a>
    <div class="post-card__body">
        <div class="post-meta">
            <?php if (!empty($post['category'])): ?><span><?= e($post['category']) ?></span><?php endif; ?>
            <time datetime="<?= e(date('Y-m-d', strtotime($post['published_at']))) ?>"><?= e(date_es($post['published_at'])) ?></time>
            <span><?= reading_time($post['content'] ?? '') ?> min de lectura</span>
        </div>
        <<?= $headTag ?>><a href="<?= e($postUrl) ?>"><?= e($post['title']) ?></a></<?= $headTag ?>>
        <p><?= e($post['excerpt'] ?: excerpt($post['content'] ?? '')) ?></p>
    </div>
</article>
