<?php
require __DIR__ . '/includes/auth.php';
$user = require_login();
$pdo = admin_db();

$stmt = $pdo->prepare('SELECT name, email, password_hash FROM users WHERE id = :id');
$stmt->execute([':id' => $user['id']]);
$account = $stmt->fetch();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim((string) ($_POST['name'] ?? ''));
    $current = (string) ($_POST['current'] ?? '');
    $new     = (string) ($_POST['new'] ?? '');
    $confirm = (string) ($_POST['confirm'] ?? '');

    if (!csrf_check($_POST['csrf'] ?? null)) {
        $errors[] = 'La sesión expiró. Vuelve a intentarlo.';
    }
    if (mb_strlen($name) < 2) {
        $errors[] = 'Escribe tu nombre (se muestra como autor de las entradas).';
    }
    if ($new !== '') {
        if (!password_verify($current, $account['password_hash'])) {
            $errors[] = 'La contraseña actual no es correcta.';
        } elseif (mb_strlen($new) < 10) {
            $errors[] = 'La nueva contraseña debe tener al menos 10 caracteres.';
        } elseif ($new !== $confirm) {
            $errors[] = 'Las contraseñas nuevas no coinciden.';
        }
    }

    if (!$errors) {
        $pdo->prepare('UPDATE users SET name = :n WHERE id = :id')->execute([':n' => $name, ':id' => $user['id']]);
        if ($new !== '') {
            $pdo->prepare('UPDATE users SET password_hash = :h WHERE id = :id')
                ->execute([':h' => password_hash($new, PASSWORD_DEFAULT), ':id' => $user['id']]);
        }
        $_SESSION['admin_name'] = $name;
        flash('Cuenta actualizada.');
        header('Location: ' . url('/admin/perfil.php'));
        exit;
    }
    $account['name'] = $name;
}

$adminTitle = 'Mi cuenta';
$adminSection = 'profile';
include __DIR__ . '/includes/layout-top.php';
?>
<div class="page-head"><h1>Mi cuenta</h1></div>

<?php if ($errors): ?><div class="notice notice--error" role="alert"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>

<form method="post" class="panel narrow stack">
    <?= csrf_field() ?>
    <label class="field"><span>Nombre</span><input type="text" name="name" required value="<?= e($account['name']) ?>"></label>
    <label class="field"><span>Correo</span><input type="email" value="<?= e($account['email']) ?>" disabled></label>
    <h2>Cambiar contraseña</h2>
    <label class="field"><span>Contraseña actual</span><input type="password" name="current" autocomplete="current-password"></label>
    <label class="field"><span>Nueva contraseña <small class="muted">mínimo 10 caracteres</small></span><input type="password" name="new" autocomplete="new-password" minlength="10"></label>
    <label class="field"><span>Repite la nueva contraseña</span><input type="password" name="confirm" autocomplete="new-password"></label>
    <button class="btn" type="submit">Guardar cambios</button>
</form>

<?php include __DIR__ . '/includes/layout-bottom.php'; ?>
