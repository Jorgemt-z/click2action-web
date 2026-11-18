<?php
/**
 * Instalación: crea el primer usuario administrador.
 * Solo funciona mientras la tabla `users` esté vacía.
 * Después de usarlo puedes borrar este archivo del servidor.
 */
require __DIR__ . '/includes/auth.php';
$pdo = admin_db();

try {
    $hasUsers = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0;
} catch (PDOException $e) {
    exit('Faltan las tablas. Importa primero database/schema.sql en tu base de datos.');
}

$errors = [];
$done = false;
$name = $email = '';

if (!$hasUsers && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim((string) ($_POST['name'] ?? ''));
    $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
    $pass  = (string) ($_POST['password'] ?? '');

    if (!csrf_check($_POST['csrf'] ?? null)) { $errors[] = 'La sesión expiró. Recarga la página.'; }
    if (mb_strlen($name) < 2) { $errors[] = 'Escribe tu nombre.'; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Escribe un correo válido.'; }
    if (mb_strlen($pass) < 10) { $errors[] = 'La contraseña debe tener al menos 10 caracteres.'; }

    if (!$errors) {
        $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (:n, :e, :h)')
            ->execute([':n' => $name, ':e' => $email, ':h' => password_hash($pass, PASSWORD_DEFAULT)]);
        $uid = (int) $pdo->lastInsertId();
        // Asigna las entradas de ejemplo al primer administrador
        $pdo->prepare('UPDATE posts SET author_id = :u WHERE author_id IS NULL')->execute([':u' => $uid]);
        $done = true;
    }
}

$adminTitle = 'Instalación';
$adminSection = '';
$user = null;
include __DIR__ . '/includes/layout-top.php';
?>
<div class="auth-card">
    <div class="auth-card__brand"><?php include __DIR__ . '/../includes/logo.php'; ?></div>
    <?php if ($hasUsers && !$done): ?>
        <h1>El panel ya está configurado</h1>
        <p class="muted">Ya existe un administrador. Por seguridad, borra el archivo <code>admin/setup.php</code> del servidor.</p>
        <a class="btn btn--block" href="<?= e(url('/admin/login.php')) ?>">Ir al ingreso</a>
    <?php elseif ($done): ?>
        <h1>¡Listo!</h1>
        <p class="muted">Tu usuario administrador fue creado. Borra el archivo <code>admin/setup.php</code> del servidor y luego ingresa.</p>
        <a class="btn btn--block" href="<?= e(url('/admin/login.php')) ?>">Ingresar al panel</a>
    <?php else: ?>
        <h1>Crear administrador</h1>
        <p class="muted">Este usuario podrá publicar entradas en el blog y ver los mensajes del formulario.</p>
        <?php if ($errors): ?><div class="notice notice--error" role="alert"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>
        <form method="post" class="stack">
            <?= csrf_field() ?>
            <label class="field"><span>Nombre</span><input type="text" name="name" required value="<?= e($name) ?>" autocomplete="name"></label>
            <label class="field"><span>Correo</span><input type="email" name="email" required value="<?= e($email) ?>" autocomplete="username"></label>
            <label class="field"><span>Contraseña <small class="muted">mínimo 10 caracteres</small></span><input type="password" name="password" required minlength="10" autocomplete="new-password"></label>
            <button class="btn btn--block" type="submit">Crear administrador</button>
        </form>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/layout-bottom.php'; ?>
