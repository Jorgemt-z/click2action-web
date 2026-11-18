<?php
/**
 * Utilidades compartidas por el sitio público y el panel.
 */

/** Escapa texto para imprimirlo en HTML. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Ruta interna respetando BASE_PATH: url('/blog') → /blog */
function url(string $path = '/'): string
{
    return BASE_PATH . '/' . ltrim($path, '/');
}

/** URL absoluta para canonical, Open Graph y sitemap. */
function abs_url(string $path = '/'): string
{
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

/** Archivo estático con versión para invalidar caché tras cada cambio. */
function asset(string $path): string
{
    $file = __DIR__ . '/../assets/' . ltrim($path, '/');
    $version = is_file($file) ? filemtime($file) : '1';
    return url('assets/' . ltrim($path, '/')) . '?v=' . $version;
}

/** Enlace de WhatsApp con mensaje pre-redactado. */
function wa_link(string $message = WHATSAPP_DEFAULT_MSG): string
{
    return 'https://wa.me/' . WHATSAPP_NUMBER . '?text=' . rawurlencode($message);
}

/** Años de experiencia calculados desde la fundación. */
function years_experience(): int
{
    return max(1, (int) date('Y') - SITE_FOUNDED);
}

/** Marca el enlace activo del menú. */
function nav_active(string $page, string $current): string
{
    return $page === $current ? ' aria-current="page"' : '';
}

/** Icono SVG del sprite definido en includes/icons.php */
function icon(string $name, string $class = 'icon'): string
{
    return '<svg class="' . e($class) . '" aria-hidden="true" focusable="false"><use href="#i-' . e($name) . '"></use></svg>';
}

// ---------------------------------------------------------------
// Seguridad: CSRF
// ---------------------------------------------------------------
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(?string $token): bool
{
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

// ---------------------------------------------------------------
// Texto
// ---------------------------------------------------------------
function slugify(string $text): string
{
    $text = mb_strtolower(trim($text));
    $map = ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n'];
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: 'entrada';
}

function excerpt(string $html, int $length = 160): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($html)));
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    $cut = mb_substr($text, 0, $length);
    $cut = mb_substr($cut, 0, (int) mb_strrpos($cut, ' '));
    return rtrim($cut, ' ,.;:') . '…';
}

function reading_time(string $html): int
{
    $words = count(preg_split('/\s+/u', trim(strip_tags($html)), -1, PREG_SPLIT_NO_EMPTY));
    return max(1, (int) ceil($words / 220));
}

function date_es(?string $date): string
{
    if (!$date) {
        return '';
    }
    $months = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $ts = strtotime($date);
    return date('j', $ts) . ' de ' . $months[(int) date('n', $ts) - 1] . ' de ' . date('Y', $ts);
}

/**
 * Limpia el HTML de las entradas del blog: solo etiquetas editoriales,
 * sin scripts, estilos en línea ni atributos de eventos.
 */
function clean_html(string $html): string
{
    $allowed = '<p><br><h2><h3><h4><strong><b><em><i><u><a><ul><ol><li><blockquote><img><figure><figcaption><hr><code><pre><table><thead><tbody><tr><th><td>';
    $html = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#is', '', $html);
    $html = strip_tags($html, $allowed);
    // Elimina atributos on*, style y class
    $html = preg_replace('/\s(on\w+|style|class|id)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
    // Neutraliza URLs javascript: / data: en href y src
    $html = preg_replace('/\s(href|src)\s*=\s*(["\']?)\s*(javascript|vbscript|data):[^"\'>\s]*\2/i', ' $1="#"', $html);
    // Enlaces externos seguros
    $html = preg_replace_callback('/<a\s([^>]*href=["\']https?:\/\/[^"\']+["\'][^>]*)>/i', function ($m) {
        if (stripos($m[1], parse_url(SITE_URL, PHP_URL_HOST)) !== false) {
            return $m[0];
        }
        return '<a ' . preg_replace('/\s(rel|target)=("[^"]*"|\'[^\']*\')/i', '', $m[1]) . ' target="_blank" rel="noopener">';
    }, $html);
    // Imágenes diferidas
    $html = preg_replace('/<img(?![^>]*loading=)/i', '<img loading="lazy" decoding="async"', $html);
    return $html;
}

// ---------------------------------------------------------------
// Blog: consultas
// ---------------------------------------------------------------
function blog_categories(): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }
    $sql = 'SELECT c.id, c.name, c.slug, COUNT(p.id) AS total
            FROM categories c
            LEFT JOIN posts p ON p.category_id = c.id AND p.status = "published" AND p.published_at <= NOW()
            GROUP BY c.id ORDER BY c.name';
    return $pdo->query($sql)->fetchAll();
}

function blog_posts(int $page = 1, ?string $categorySlug = null, ?string $search = null): array
{
    $pdo = db();
    if (!$pdo) {
        return ['items' => [], 'total' => 0, 'pages' => 0];
    }
    $where = ['p.status = "published"', 'p.published_at <= NOW()'];
    $params = [];
    if ($categorySlug) {
        $where[] = 'c.slug = :cat';
        $params[':cat'] = $categorySlug;
    }
    if ($search) {
        $where[] = '(p.title LIKE :q OR p.excerpt LIKE :q2)';
        $params[':q'] = '%' . $search . '%';
        $params[':q2'] = '%' . $search . '%';
    }
    $whereSql = 'WHERE ' . implode(' AND ', $where);

    $count = $pdo->prepare("SELECT COUNT(*) FROM posts p LEFT JOIN categories c ON c.id = p.category_id $whereSql");
    $count->execute($params);
    $total = (int) $count->fetchColumn();

    $offset = max(0, ($page - 1) * POSTS_PER_PAGE);
    $stmt = $pdo->prepare(
        "SELECT p.id, p.title, p.slug, p.excerpt, p.content, p.cover_image, p.cover_alt, p.published_at,
                c.name AS category, c.slug AS category_slug, u.name AS author
         FROM posts p
         LEFT JOIN categories c ON c.id = p.category_id
         LEFT JOIN users u ON u.id = p.author_id
         $whereSql
         ORDER BY p.published_at DESC
         LIMIT " . (int) POSTS_PER_PAGE . " OFFSET " . (int) $offset
    );
    $stmt->execute($params);

    return [
        'items' => $stmt->fetchAll(),
        'total' => $total,
        'pages' => (int) ceil($total / POSTS_PER_PAGE),
    ];
}

function blog_latest(int $limit = 3, int $excludeId = 0): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }
    $stmt = $pdo->prepare(
        'SELECT p.id, p.title, p.slug, p.excerpt, p.content, p.cover_image, p.cover_alt, p.published_at, c.name AS category
         FROM posts p LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.status = "published" AND p.published_at <= NOW() AND p.id <> :ex
         ORDER BY p.published_at DESC LIMIT ' . (int) $limit
    );
    $stmt->execute([':ex' => $excludeId]);
    return $stmt->fetchAll();
}

function blog_post(string $slug): ?array
{
    $pdo = db();
    if (!$pdo) {
        return null;
    }
    $stmt = $pdo->prepare(
        'SELECT p.*, c.name AS category, c.slug AS category_slug, u.name AS author
         FROM posts p
         LEFT JOIN categories c ON c.id = p.category_id
         LEFT JOIN users u ON u.id = p.author_id
         WHERE p.slug = :slug AND p.status = "published" AND p.published_at <= NOW()
         LIMIT 1'
    );
    $stmt->execute([':slug' => $slug]);
    $post = $stmt->fetch();
    return $post ?: null;
}

/** URL pública de la imagen de portada (o null). */
function cover_url(?string $file): ?string
{
    if (!$file) {
        return null;
    }
    if (preg_match('#^https?://#', $file)) {
        return $file;
    }
    return url(UPLOAD_URL . $file);
}
