<?php
/**
 * Conexión PDO perezosa: solo se abre cuando una página la necesita.
 * Si la base de datos no está disponible, db() devuelve null y el sitio
 * sigue funcionando (el blog muestra un estado vacío).
 */

function db(): ?PDO
{
    static $pdo = null;
    static $failed = false;

    if ($pdo instanceof PDO || $failed) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        $failed = true;
        error_log('[Click 2 Action] Error de conexión a MySQL: ' . $e->getMessage());
        $pdo = null;
    }

    return $pdo;
}
