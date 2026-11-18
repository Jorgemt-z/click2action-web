<?php
/**
 * SOLO PARA DESARROLLO LOCAL con el servidor integrado de PHP:
 *   php -S localhost:8000 router.php
 * Imita las reglas del .htaccess. En el hosting (Apache) este archivo no se usa.
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = __DIR__;

if (preg_match('#^/(config|includes|database|admin/includes)(/|$)#', $path)) {
    http_response_code(403);
    exit('Prohibido');
}
if ($path !== '/' && is_file($root . $path)) {
    return false; // archivo estático o .php existente
}
if ($path === '/admin' || $path === '/admin/') {
    require $root . '/admin/index.php';
    return true;
}
if ($path === '/sitemap.xml') {
    require $root . '/sitemap.php';
    return true;
}
if (preg_match('#^/blog/categoria/([a-z0-9-]+)/?$#', $path, $m)) {
    $_GET['categoria'] = $m[1];
    require $root . '/blog.php';
    return true;
}
if (preg_match('#^/blog/([a-z0-9-]+)/?$#', $path, $m)) {
    $_GET['slug'] = $m[1];
    require $root . '/articulo.php';
    return true;
}
$page = trim($path, '/') ?: 'index';
if (preg_match('#^[a-z0-9-]+$#', $page) && is_file("$root/$page.php")) {
    require "$root/$page.php";
    return true;
}
require $root . '/404.php';
return true;
