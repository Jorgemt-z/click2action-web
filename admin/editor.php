<?php
require __DIR__ . '/includes/auth.php';
$user = require_login();
$pdo = admin_db();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$post = [
    'id' => 0, 'title' => '', 'slug' => '', 'excerpt' => '', 'content' => '', 'cover_image' => '',
    'cover_alt' => '', 'category_id' => '', 'status' => 'draft', 'meta_title' => '', 'meta_description' => '',
    'published_at' => date('Y-m-d H:i:s'),
];
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash('Esa entrada no existe.', 'error');
        header('Location: ' . url('/admin/'));
        exit;
    }
    $post = $found;
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$errors = [];

/** Sube la imagen de portada y devuelve el nombre del archivo. */
function upload_cover(array $file, array &$errors): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors['cover'] = 'No se pudo subir la imagen. Inténtalo de nuevo.';
        return null;
    }
    if ($file['size'] > UPLOAD_MAX_BYTES) {
        $errors['cover'] = 'La imagen pesa más de ' . (UPLOAD_MAX_BYTES / 1024 / 1024) . ' MB. Comprímela antes de subirla.';
        return null;
    }
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($types[$mime]) || !getimagesize($file['tmp_name'])) {
        $errors['cover'] = 'Formato no permitido. Usa JPG, PNG o WEBP.';
        return null;
    }
    if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true)) {
        $errors['cover'] = 'No existe la carpeta uploads/blog y no se pudo crear.';
        return null;
    }
    $name = date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $types[$mime];
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name)) {
        $errors['cover'] = 'No se pudo guardar la imagen en el servidor.';
        return null;
    }
    return $name;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $errors['general'] = 'La sesión expiró. Copia tu texto, recarga la página y vuelve a guardar.';
    }

    $post['title']            = trim((string) ($_POST['title'] ?? ''));
    $post['slug']             = slugify(trim((string) ($_POST['slug'] ?? '')) ?: $post['title']);
    $post['excerpt']          = trim((string) ($_POST['excerpt'] ?? ''));
    $post['content']          = (string) ($_POST['content'] ?? '');
    $post['cover_alt']        = trim((string) ($_POST['cover_alt'] ?? ''));
    $post['category_id']      = (int) ($_POST['category_id'] ?? 0) ?: null;
    $post['status']           = ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft';
    $post['meta_title']       = trim((string) ($_POST['meta_title'] ?? ''));
    $post['meta_description'] = trim((string) ($_POST['meta_description'] ?? ''));
    $dateInput                = trim((string) ($_POST['published_at'] ?? ''));
    $post['published_at']     = $dateInput ? date('Y-m-d H:i:s', strtotime($dateInput)) : date('Y-m-d H:i:s');

    if (mb_strlen($post['title']) < 5) {
        $errors['title'] = 'El título debe tener al menos 5 caracteres.';
    }
    if (mb_strlen(trim(strip_tags($post['content']))) < 50) {
        $errors['content'] = 'El contenido es muy corto (mínimo 50 caracteres).';
    }
    if (mb_strlen($post['excerpt']) > 300) {
        $errors['excerpt'] = 'El resumen no debe superar 300 caracteres.';
    }
    if (mb_strlen($post['meta_title']) > 70) {
        $errors['meta_title'] = 'El título SEO no debe superar 70 caracteres.';
    }
    if (mb_strlen($post['meta_description']) > 170) {
        $errors['meta_description'] = 'La descripción SEO no debe superar 170 caracteres.';
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM posts WHERE slug = :s AND id <> :id');
    $stmt->execute([':s' => $post['slug'], ':id' => $id]);
    if ($stmt->fetchColumn()) {
        $errors['slug'] = 'Ya existe otra entrada con esta URL. Cámbiala para que sea única.';
    }

    if (!$errors) {
        $newCover = upload_cover($_FILES['cover'] ?? [], $errors);
        $oldCover = $post['cover_image'] ?? '';
        if ($newCover) {
            $post['cover_image'] = $newCover;
        } elseif (!empty($_POST['remove_cover'])) {
            $post['cover_image'] = null;
        }
    }

    if (!$errors) {
        $params = [
            ':title' => $post['title'], ':slug' => $post['slug'], ':excerpt' => $post['excerpt'] ?: null,
            ':content' => clean_html($post['content']), ':cover' => $post['cover_image'] ?: null,
            ':cover_alt' => $post['cover_alt'] ?: null, ':cat' => $post['category_id'], ':status' => $post['status'],
            ':mt' => $post['meta_title'] ?: null, ':md' => $post['meta_description'] ?: null, ':pub' => $post['published_at'],
        ];
        if ($id) {
            $params[':id'] = $id;
            $pdo->prepare(
                'UPDATE posts SET title=:title, slug=:slug, excerpt=:excerpt, content=:content, cover_image=:cover,
                 cover_alt=:cover_alt, category_id=:cat, status=:status, meta_title=:mt, meta_description=:md,
                 published_at=:pub, updated_at=NOW() WHERE id=:id'
            )->execute($params);
        } else {
            $params[':author'] = $user['id'];
            $pdo->prepare(
                'INSERT INTO posts (title, slug, excerpt, content, cover_image, cover_alt, category_id, status, meta_title, meta_description, published_at, author_id)
                 VALUES (:title, :slug, :excerpt, :content, :cover, :cover_alt, :cat, :status, :mt, :md, :pub, :author)'
            )->execute($params);
            $id = (int) $pdo->lastInsertId();
        }
        // Borra la portada anterior si se reemplazó o se quitó
        if (!empty($oldCover) && $oldCover !== $post['cover_image'] && is_file(UPLOAD_DIR . $oldCover)) {
            @unlink(UPLOAD_DIR . $oldCover);
        }
        flash($post['status'] === 'published' ? 'Entrada publicada.' : 'Borrador guardado.');
        header('Location: ' . url('/admin/editor.php?id=' . $id));
        exit;
    }
}

$adminTitle = $id ? 'Editar entrada' : 'Nueva entrada';
$adminSection = $id ? 'posts' : 'new';
include __DIR__ . '/includes/layout-top.php';
$err = fn($k) => isset($errors[$k]) ? '<p class="error">' . e($errors[$k]) . '</p>' : '';
?>
<div class="page-head">
    <div>
        <a class="back" href="<?= e(url('/admin/')) ?>">← Entradas</a>
        <h1><?= e($adminTitle) ?></h1>
    </div>
    <?php if ($id && $post['status'] === 'published'): ?>
        <a class="btn btn--ghost" href="<?= e(url('/blog/' . $post['slug'])) ?>" target="_blank" rel="noopener">Ver en el sitio <?= icon('arrow-up') ?></a>
    <?php endif; ?>
</div>

<?php if ($errors): ?>
    <div class="notice notice--error" role="alert"><?= e($errors['general'] ?? 'Revisa los campos marcados antes de guardar.') ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="editor" data-editor>
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $id ?>">

    <div class="editor__main">
        <label class="field">
            <span>Título</span>
            <input type="text" name="title" required maxlength="200" value="<?= e($post['title']) ?>" data-title class="input-lg">
            <?= $err('title') ?>
        </label>

        <label class="field">
            <span>URL <small class="muted">click2action.com.co/blog/<b data-slug-preview><?= e($post['slug']) ?></b></small></span>
            <input type="text" name="slug" maxlength="220" value="<?= e($post['slug']) ?>" data-slug placeholder="se-genera-automaticamente">
            <?= $err('slug') ?>
        </label>

        <div class="field">
            <span id="content-label">Contenido</span>
            <div class="toolbar" role="toolbar" aria-label="Formato del contenido">
                <button type="button" data-wrap="h2" title="Subtítulo">H2</button>
                <button type="button" data-wrap="h3" title="Subtítulo menor">H3</button>
                <button type="button" data-wrap="p" title="Párrafo">¶</button>
                <button type="button" data-wrap="strong" title="Negrita"><b>N</b></button>
                <button type="button" data-wrap="em" title="Cursiva"><i>C</i></button>
                <button type="button" data-list="ul" title="Lista con viñetas">• Lista</button>
                <button type="button" data-list="ol" title="Lista numerada">1. Lista</button>
                <button type="button" data-wrap="blockquote" title="Cita">“ Cita</button>
                <button type="button" data-link title="Enlace">Enlace</button>
                <button type="button" data-preview title="Vista previa" aria-pressed="false">Vista previa</button>
            </div>
            <textarea name="content" rows="22" aria-labelledby="content-label" data-content><?= e($post['content']) ?></textarea>
            <div class="preview prose" data-preview-pane hidden></div>
            <p class="hint">Escribe en HTML sencillo o usa los botones. Cada párrafo va entre &lt;p&gt; y &lt;/p&gt;. Usa H2 para las secciones: ayuda al SEO.</p>
            <?= $err('content') ?>
        </div>

        <label class="field">
            <span>Resumen <small class="muted">(aparece en el listado del blog)</small></span>
            <textarea name="excerpt" rows="3" maxlength="300" data-count="300"><?= e($post['excerpt']) ?></textarea>
            <?= $err('excerpt') ?>
        </label>
    </div>

    <aside class="editor__side">
        <section class="panel">
            <h2>Publicación</h2>
            <label class="field">
                <span>Estado</span>
                <select name="status">
                    <option value="draft"<?= $post['status'] === 'draft' ? ' selected' : '' ?>>Borrador</option>
                    <option value="published"<?= $post['status'] === 'published' ? ' selected' : '' ?>>Publicada</option>
                </select>
            </label>
            <label class="field">
                <span>Fecha de publicación</span>
                <input type="datetime-local" name="published_at" value="<?= e(date('Y-m-d\TH:i', strtotime($post['published_at'] ?: 'now'))) ?>">
                <small class="muted">Si eliges una fecha futura, la entrada se publica sola ese día.</small>
            </label>
            <label class="field">
                <span>Categoría</span>
                <select name="category_id">
                    <option value="">Sin categoría</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"<?= (int) $post['category_id'] === (int) $c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="btn btn--block" type="submit">Guardar</button>
        </section>

        <section class="panel">
            <h2>Imagen de portada</h2>
            <?php if (!empty($post['cover_image'])): ?>
                <img class="cover-preview" src="<?= e(cover_url($post['cover_image'])) ?>" alt="">
                <label class="check"><input type="checkbox" name="remove_cover" value="1"> Quitar imagen</label>
            <?php endif; ?>
            <label class="field">
                <span>Subir imagen <small class="muted">JPG, PNG o WEBP, máx. 3 MB, ideal 1600×800</small></span>
                <input type="file" name="cover" accept="image/jpeg,image/png,image/webp">
                <?= $err('cover') ?>
            </label>
            <label class="field">
                <span>Texto alternativo</span>
                <input type="text" name="cover_alt" maxlength="200" value="<?= e($post['cover_alt']) ?>" placeholder="Describe la imagen para Google y lectores de pantalla">
            </label>
        </section>

        <section class="panel">
            <h2>SEO</h2>
            <label class="field">
                <span>Título SEO</span>
                <input type="text" name="meta_title" maxlength="70" value="<?= e($post['meta_title']) ?>" data-count="60" placeholder="Si lo dejas vacío se usa el título">
                <?= $err('meta_title') ?>
            </label>
            <label class="field">
                <span>Descripción SEO</span>
                <textarea name="meta_description" rows="3" maxlength="170" data-count="155" placeholder="Resumen de 140 a 155 caracteres para Google"><?= e($post['meta_description']) ?></textarea>
                <?= $err('meta_description') ?>
            </label>
            <div class="serp" aria-hidden="true">
                <span class="serp__url">click2action.com.co › blog › <span data-slug-preview><?= e($post['slug']) ?></span></span>
                <span class="serp__title" data-serp-title><?= e($post['meta_title'] ?: $post['title'] ?: 'Título de la entrada') ?></span>
                <span class="serp__desc" data-serp-desc><?= e($post['meta_description'] ?: $post['excerpt'] ?: 'La descripción aparecerá aquí.') ?></span>
            </div>
        </section>
    </aside>
</form>

<?php include __DIR__ . '/includes/layout-bottom.php'; ?>
