<?php
require __DIR__ . '/includes/auth.php';

if (current_user()) {
    header('Location: ' . url('/admin/'));
    exit;
}

$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    if (!csrf_check($_POST['csrf'] ?? null)) {
        $error = 'La sesión expiró. Vuelve a intentarlo.';
    } elseif (too_many_attempts($ip)) {
        $error = 'Demasiados intentos fallidos. Espera ' . LOGIN_WINDOW_MIN . ' minutos e inténtalo de nuevo.';
    } elseif (attempt_login($email, (string) ($_POST['password'] ?? ''))) {
        header('Location: ' . url('/admin/'));
        exit;
    } else {
        $error = 'Correo o contraseña incorrectos.';
    }
}

$adminTitle = 'Ingresar';
$adminSection = '';
$user = null;
include __DIR__ . '/includes/layout-top.php';
?>
<div class="auth-card">
    <div class="auth-card__brand"><?php include __DIR__ . '/../includes/logo.php'; ?></div>
    <h1>Panel de Click 2 Action</h1>
    <p class="muted">Ingresa para administrar el blog y los mensajes.</p>

    <?php if ($error): ?><div class="notice notice--error" role="alert"><?= e($error) ?></div><?php endif; ?>

    <form method="post" class="stack">
        <?= csrf_field() ?>
        <label class="field">
            <span>Correo</span>
            <input type="email" name="email" required autocomplete="username" value="<?= e($email) ?>" autofocus>
        </label>
        <label class="field">
            <span>Contraseña</span>
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        <button class="btn btn--block" type="submit">Ingresar</button>
    </form>
</div>
<?php include __DIR__ . '/includes/layout-bottom.php'; ?>
