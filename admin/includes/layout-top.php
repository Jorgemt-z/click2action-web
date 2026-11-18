<?php
/** Cabecera del panel. Requiere $adminTitle y $adminSection. Opcional $user. */
$unread = 0;
if (!empty($user)) {
    $unread = (int) admin_db()->query('SELECT COUNT(*) FROM leads WHERE is_read = 0')->fetchColumn();
}
$flash = flash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($adminTitle) ?> · Panel Click 2 Action</title>
    <link rel="icon" href="<?= e(url('assets/img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600..800&family=Manrope:wght@400..700&display=swap">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
    <script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</head>
<body class="admin<?= empty($user) ? ' admin--auth' : '' ?>">
<?php include __DIR__ . '/../../includes/icons.php'; ?>
<?php if (!empty($user)): ?>
<header class="admin-bar">
    <a class="admin-brand" href="<?= e(url('/admin/')) ?>">
        <?php include __DIR__ . '/../../includes/logo.php'; ?>
        <span>Panel</span>
    </a>
    <nav class="admin-nav" aria-label="Panel">
        <a href="<?= e(url('/admin/')) ?>"<?= $adminSection === 'posts' ? ' aria-current="page"' : '' ?>>Entradas</a>
        <a href="<?= e(url('/admin/editor.php')) ?>"<?= $adminSection === 'new' ? ' aria-current="page"' : '' ?>>Nueva entrada</a>
        <a href="<?= e(url('/admin/clientes.php')) ?>"<?= $adminSection === 'clients' ? ' aria-current="page"' : '' ?>>Clientes</a>
        <a href="<?= e(url('/admin/equipo.php')) ?>"<?= $adminSection === 'team' ? ' aria-current="page"' : '' ?>>Equipo</a>
        <a href="<?= e(url('/admin/mensajes.php')) ?>"<?= $adminSection === 'leads' ? ' aria-current="page"' : '' ?>>Mensajes<?php if ($unread): ?> <span class="badge"><?= $unread ?></span><?php endif; ?></a>
        <a href="<?= e(url('/admin/perfil.php')) ?>"<?= $adminSection === 'profile' ? ' aria-current="page"' : '' ?>>Mi cuenta</a>
    </nav>
    <div class="admin-user">
        <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener">Ver sitio <?= icon('arrow-up') ?></a>
        <form action="<?= e(url('/admin/logout.php')) ?>" method="post"><?= csrf_field() ?><button type="submit" class="linklike">Salir</button></form>
    </div>
</header>
<?php endif; ?>
<main class="admin-main">
<?php if ($flash): ?>
    <div class="notice notice--<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
<?php endif; ?>
