<?php
require __DIR__ . '/includes/auth.php';
$user = require_login();
$pdo = admin_db();

// Eliminar entrada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (csrf_check($_POST['csrf'] ?? null)) {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT cover_image FROM posts WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $cover = $stmt->fetchColumn();
        $pdo->prepare('DELETE FROM posts WHERE id = :id')->execute([':id' => $id]);
        if ($cover && is_file(UPLOAD_DIR . $cover)) {
            @unlink(UPLOAD_DIR . $cover);
        }
        flash('La entrada se eliminó.');
    }
    header('Location: ' . url('/admin/'));
    exit;
}

$status = in_array($_GET['estado'] ?? '', ['published', 'draft'], true) ? $_GET['estado'] : '';
$where = $status ? 'WHERE p.status = :s' : '';
$stmt = $pdo->prepare(
    "SELECT p.id, p.title, p.slug, p.status, p.published_at, p.updated_at, p.created_at, c.name AS category
     FROM posts p LEFT JOIN categories c ON c.id = p.category_id
     $where ORDER BY COALESCE(p.published_at, p.created_at) DESC"
);
$stmt->execute($status ? [':s' => $status] : []);
$posts = $stmt->fetchAll();

$counts = $pdo->query("SELECT status, COUNT(*) n FROM posts GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

$adminTitle = 'Entradas';
$adminSection = 'posts';
include __DIR__ . '/includes/layout-top.php';
?>
<div class="page-head">
    <div>
        <h1>Entradas del blog</h1>
        <p class="muted"><?= (int) ($counts['published'] ?? 0) ?> publicadas · <?= (int) ($counts['draft'] ?? 0) ?> borradores</p>
    </div>
    <a class="btn" href="<?= e(url('/admin/editor.php')) ?>"><?= icon('plus') ?> Nueva entrada</a>
</div>

<nav class="tabs" aria-label="Filtrar por estado">
    <a href="<?= e(url('/admin/')) ?>"<?= !$status ? ' aria-current="page"' : '' ?>>Todas</a>
    <a href="<?= e(url('/admin/?estado=published')) ?>"<?= $status === 'published' ? ' aria-current="page"' : '' ?>>Publicadas</a>
    <a href="<?= e(url('/admin/?estado=draft')) ?>"<?= $status === 'draft' ? ' aria-current="page"' : '' ?>>Borradores</a>
</nav>

<?php if ($posts): ?>
<div class="table-wrap">
    <table class="table">
        <thead>
            <tr><th>Título</th><th>Categoría</th><th>Estado</th><th>Fecha</th><th><span class="visually-hidden">Acciones</span></th></tr>
        </thead>
        <tbody>
        <?php foreach ($posts as $p):
            $scheduled = $p['status'] === 'published' && strtotime($p['published_at']) > time(); ?>
            <tr>
                <td class="table__title"><a href="<?= e(url('/admin/editor.php?id=' . $p['id'])) ?>"><?= e($p['title']) ?></a><small>/blog/<?= e($p['slug']) ?></small></td>
                <td><?= e($p['category'] ?: '—') ?></td>
                <td>
                    <?php if ($scheduled): ?><span class="pill pill--scheduled">Programada</span>
                    <?php elseif ($p['status'] === 'published'): ?><span class="pill pill--ok">Publicada</span>
                    <?php else: ?><span class="pill">Borrador</span><?php endif; ?>
                </td>
                <td class="nowrap"><?= e(date('d/m/Y', strtotime($p['published_at'] ?: $p['created_at']))) ?></td>
                <td class="table__actions">
                    <?php if ($p['status'] === 'published' && !$scheduled): ?>
                        <a href="<?= e(url('/blog/' . $p['slug'])) ?>" target="_blank" rel="noopener">Ver</a>
                    <?php endif; ?>
                    <a href="<?= e(url('/admin/editor.php?id=' . $p['id'])) ?>">Editar</a>
                    <form method="post" data-confirm="¿Eliminar «<?= e($p['title']) ?>»? Esta acción no se puede deshacer.">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <button type="submit" class="linklike danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <div class="empty">
        <h2>Todavía no hay entradas aquí</h2>
        <p class="muted">Escribe tu primer artículo: el blog ayuda a que Google encuentre tu sitio.</p>
        <a class="btn" href="<?= e(url('/admin/editor.php')) ?>">Escribir una entrada</a>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/layout-bottom.php'; ?>
