<?php
/**
 * Autenticación del panel de administración.
 */
require_once __DIR__ . '/../../includes/bootstrap.php';

header('X-Robots-Tag: noindex, nofollow', true);
header('X-Frame-Options: DENY');

const LOGIN_MAX_ATTEMPTS = 5;      // intentos fallidos permitidos
const LOGIN_WINDOW_MIN   = 15;     // en esta ventana de minutos
const SESSION_IDLE_MIN   = 120;    // cierre de sesión por inactividad

function admin_db(): PDO
{
    $pdo = db();
    if (!$pdo) {
        http_response_code(500);
        exit('No hay conexión con la base de datos. Revisa los datos en config/config.php e importa database/schema.sql.');
    }
    return $pdo;
}

function current_user(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    if (time() - ($_SESSION['admin_seen'] ?? 0) > SESSION_IDLE_MIN * 60) {
        logout();
        return null;
    }
    $_SESSION['admin_seen'] = time();
    return ['id' => (int) $_SESSION['admin_id'], 'name' => $_SESSION['admin_name'] ?? 'Admin'];
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        header('Location: ' . url('/admin/login.php'));
        exit;
    }
    return $user;
}

function too_many_attempts(string $ip): bool
{
    $stmt = admin_db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = :ip AND attempted_at > (NOW() - INTERVAL ' . LOGIN_WINDOW_MIN . ' MINUTE)');
    $stmt->execute([':ip' => $ip]);
    return (int) $stmt->fetchColumn() >= LOGIN_MAX_ATTEMPTS;
}

function attempt_login(string $email, string $password): bool
{
    $pdo = admin_db();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    $stmt = $pdo->prepare('SELECT id, name, password_hash FROM users WHERE email = :e LIMIT 1');
    $stmt->execute([':e' => mb_strtolower(trim($email))]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $pdo->prepare('INSERT INTO login_attempts (ip) VALUES (:ip)')->execute([':ip' => $ip]);
        return false;
    }

    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $pdo->prepare('UPDATE users SET password_hash = :h WHERE id = :id')
            ->execute([':h' => password_hash($password, PASSWORD_DEFAULT), ':id' => $user['id']]);
    }

    session_regenerate_id(true);
    $_SESSION['admin_id']   = (int) $user['id'];
    $_SESSION['admin_name'] = $user['name'];
    $_SESSION['admin_seen'] = time();
    $pdo->prepare('DELETE FROM login_attempts WHERE ip = :ip')->execute([':ip' => $ip]);
    $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id')->execute([':id' => $user['id']]);
    return true;
}

function logout(): void
{
    unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_seen']);
    session_regenerate_id(true);
}

/** Mensajes flash entre redirecciones del panel. */
function flash(?string $message = null, string $type = 'success'): ?array
{
    if ($message !== null) {
        $_SESSION['admin_flash'] = ['message' => $message, 'type' => $type];
        return null;
    }
    $f = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);
    return $f;
}
