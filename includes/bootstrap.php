<?php
/**
 * Arranque común: configuración, sesión, base de datos y utilidades.
 * Cada página pública y del panel incluye este archivo primero.
 */

if (!is_file(__DIR__ . '/../config/config.php')) {
    http_response_code(500);
    exit('Falta config/config.php: copia config/config.example.php con ese nombre y llena tus datos.');
}
require_once __DIR__ . '/../config/config.php';

if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

date_default_timezone_set('America/Bogota');
mb_internal_encoding('UTF-8');

if (session_status() === PHP_SESSION_NONE) {
    session_name('c2a_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => BASE_PATH === '' ? '/' : BASE_PATH . '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/data.php';
