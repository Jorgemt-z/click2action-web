<?php
/** Tarjeta de caso de éxito. Requiere $case. */
$caseLogo = null;
foreach ($CLIENTS as $c) {
    if ($c['name'] === $case['client']) {
        $caseLogo = $c['logo'];
        break;
    }
}
?>
<article class="case reveal">
    <div class="case__plate">
        <?php if ($caseLogo): ?>
            <img src="<?= e($caseLogo) ?>" alt="Logo de <?= e($case['client']) ?>" loading="lazy" decoding="async">
        <?php else: ?>
            <span class="case__plate-name" aria-hidden="true"><?= e($case['client']) ?></span>
        <?php endif; ?>
    </div>
    <div class="case__body">
        <p class="case__client"><?= e($case['client']) ?></p>
        <h3><?= e($case['title']) ?></h3>
        <p><?= e($case['summary']) ?></p>
        <ul class="tags" aria-label="Servicios aplicados">
            <?php foreach ($case['tags'] as $tag): ?><li><?= e($tag) ?></li><?php endforeach; ?>
        </ul>
    </div>
</article>
