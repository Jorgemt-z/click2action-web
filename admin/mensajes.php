<?php
require __DIR__ . '/includes/auth.php';
$user = require_login();
$pdo = admin_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $id = (int) ($_POST['id'] ?? 0);
    switch ($_POST['action'] ?? '') {
        case 'read':
            $pdo->prepare('UPDATE leads SET is_read = 1 WHERE id = :id')->execute([':id' => $id]);
            break;
        case 'unread':
            $pdo->prepare('UPDATE leads SET is_read = 0 WHERE id = :id')->execute([':id' => $id]);
            break;
        case 'delete':
            $pdo->prepare('DELETE FROM leads WHERE id = :id')->execute([':id' => $id]);
            flash('Mensaje eliminado.');
            break;
    }
    header('Location: ' . url('/admin/mensajes.php'));
    exit;
}

// Exportar a CSV (abre en Excel)
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="mensajes-click2action-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Fecha', 'Nombre', 'Empresa', 'Correo', 'Celular', 'Motivo', 'Mensaje'], ';');
    foreach ($pdo->query('SELECT created_at, name, company, email, phone, reason, message FROM leads ORDER BY created_at DESC') as $row) {
        fputcsv($out, array_values($row), ';');
    }
    exit;
}

$leads = $pdo->query('SELECT * FROM leads ORDER BY created_at DESC LIMIT 300')->fetchAll();

$adminTitle = 'Mensajes';
$adminSection = 'leads';
include __DIR__ . '/includes/layout-top.php';
?>
<div class="page-head">
    <div>
        <h1>Mensajes del formulario</h1>
        <p class="muted">Los mensajes también llegan a <?= e(FORM_TO_EMAIL) ?>.</p>
    </div>
    <?php if ($leads): ?><a class="btn btn--ghost" href="?export=csv">Descargar CSV</a><?php endif; ?>
</div>

<?php if ($leads): ?>
<div class="leads">
    <?php foreach ($leads as $l):
        $phoneDigits = preg_replace('/\D/', '', $l['phone']);
        if (strlen($phoneDigits) === 10) { $phoneDigits = '57' . $phoneDigits; } ?>
        <article class="lead<?= $l['is_read'] ? '' : ' lead--new' ?>">
            <header class="lead__head">
                <div>
                    <h2><?= e($l['name']) ?><?= $l['company'] ? ' · <span class="muted">' . e($l['company']) . '</span>' : '' ?></h2>
                    <p class="muted"><?= e($l['reason']) ?> · <?= e(date('d/m/Y H:i', strtotime($l['created_at']))) ?></p>
                </div>
                <?php if (!$l['is_read']): ?><span class="pill pill--ok">Nuevo</span><?php endif; ?>
            </header>
            <p class="lead__msg"><?= nl2br(e($l['message'])) ?></p>
            <div class="lead__actions">
                <a class="btn btn--sm btn--wa" href="https://wa.me/<?= e($phoneDigits) ?>?text=<?= rawurlencode('Hola ' . explode(' ', $l['name'])[0] . ', te escribimos de Click 2 Action por el mensaje que nos dejaste en la web.') ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> WhatsApp</a>
                <a class="btn btn--sm btn--ghost" href="mailto:<?= e($l['email']) ?>"><?= e($l['email']) ?></a>
                <a class="btn btn--sm btn--ghost" href="tel:<?= e($l['phone']) ?>"><?= e($l['phone']) ?></a>
                <form method="post">
                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
                    <input type="hidden" name="action" value="<?= $l['is_read'] ? 'unread' : 'read' ?>">
                    <button class="linklike" type="submit"><?= $l['is_read'] ? 'Marcar como no leído' : 'Marcar como leído' ?></button>
                </form>
                <form method="post" data-confirm="¿Eliminar el mensaje de <?= e($l['name']) ?>?">
                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
                    <input type="hidden" name="action" value="delete">
                    <button class="linklike danger" type="submit">Eliminar</button>
                </form>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?php else: ?>
    <div class="empty">
        <h2>Aún no hay mensajes</h2>
        <p class="muted">Cuando alguien llene el formulario de contacto, aparecerá aquí.</p>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/layout-bottom.php'; ?>
