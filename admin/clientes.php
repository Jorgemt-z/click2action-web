<?php
require __DIR__ . '/includes/auth.php';
$user = require_login();
$pdo = admin_db();
require __DIR__ . '/includes/uploads.php';

const LOGO_MAX_BYTES = 1024 * 1024; // 1 MB

// ------------------------------------------------------------------
// Primera vez: crea las tablas y copia los clientes iniciales
// ------------------------------------------------------------------
$pdo->exec('CREATE TABLE IF NOT EXISTS clients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    logo VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
$pdo->exec('CREATE TABLE IF NOT EXISTS settings (
    name VARCHAR(60) PRIMARY KEY,
    value VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

if (!$pdo->query("SELECT value FROM settings WHERE name = 'clients_seeded'")->fetchColumn()) {
    $pdo->beginTransaction();
    $insert = $pdo->prepare('INSERT INTO clients (name, logo, sort_order) VALUES (:name, :logo, :ord)');
    foreach ($CLIENTS_DEFAULT as $i => $c) {
        $insert->execute([':name' => $c['name'], ':logo' => client_logo_file($c['slug']), ':ord' => ($i + 1) * 10]);
    }
    $set = $pdo->prepare('INSERT IGNORE INTO settings (name, value) VALUES (:n, :v)');
    $set->execute([':n' => 'team_size', ':v' => (string) TEAM_SIZE]);
    $set->execute([':n' => 'active_brands', ':v' => (string) ACTIVE_BRANDS]);
    $set->execute([':n' => 'clients_seeded', ':v' => '1']);
    $pdo->commit();
}

function find_client(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM clients WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

/** Borra un logo solo si fue subido desde el panel (nunca los incluidos con el sitio). */
function delete_uploaded_logo(?string $rel): void
{
    delete_upload($rel, 'clientes');
}

/** Sube un logo y devuelve su ruta relativa a la raíz, o null. */
function upload_logo(array $file, string $name, array &$errors): ?string
{
    return upload_image($file, 'clientes', $name, LOGO_MAX_BYTES,
        ['image/png' => 'png', 'image/webp' => 'webp', 'image/jpeg' => 'jpg'], $errors, 'logo');
}

// ------------------------------------------------------------------
// Acciones
// ------------------------------------------------------------------
$errors = [];
$editId = (int) ($_GET['editar'] ?? 0);
$form = ['id' => 0, 'name' => '', 'logo' => null, 'is_active' => 1];
if ($editId && ($found = find_client($pdo, $editId))) {
    $form = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash('La sesión expiró. Vuelve a intentarlo.', 'error');
        header('Location: ' . url('/admin/clientes.php'));
        exit;
    }
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'figures') {
        $team   = (int) ($_POST['team_size'] ?? 0);
        $brands = (int) ($_POST['active_brands'] ?? 0);
        if ($team < 1 || $team > 9999 || $brands < 1 || $brands > 9999) {
            flash('Las cifras deben ser números entre 1 y 9999.', 'error');
        } else {
            $set = $pdo->prepare('REPLACE INTO settings (name, value) VALUES (:n, :v)');
            $set->execute([':n' => 'team_size', ':v' => (string) $team]);
            $set->execute([':n' => 'active_brands', ':v' => (string) $brands]);
            flash('Cifras actualizadas en todo el sitio.');
        }
        header('Location: ' . url('/admin/clientes.php'));
        exit;
    }

    if ($action === 'save') {
        $existing = $id ? find_client($pdo, $id) : null;
        $form = [
            'id'        => $existing ? $id : 0,
            'name'      => trim((string) ($_POST['name'] ?? '')),
            'logo'      => $existing['logo'] ?? null,
            'is_active' => empty($_POST['is_active']) ? 0 : 1,
        ];
        $editId = $form['id'];
        if (mb_strlen($form['name']) < 2 || mb_strlen($form['name']) > 120) {
            $errors['name'] = 'Escribe el nombre del cliente (entre 2 y 120 caracteres).';
        }
        $newLogo = $errors ? null : upload_logo($_FILES['logo'] ?? [], $form['name'], $errors);

        if (!$errors) {
            $oldLogo = $form['logo'];
            if ($newLogo) {
                $form['logo'] = $newLogo;
            } elseif (!empty($_POST['remove_logo'])) {
                $form['logo'] = null;
            }
            if ($existing) {
                $pdo->prepare('UPDATE clients SET name = :name, logo = :logo, is_active = :active WHERE id = :id')
                    ->execute([':name' => $form['name'], ':logo' => $form['logo'], ':active' => $form['is_active'], ':id' => $id]);
                flash('Cliente actualizado.');
            } else {
                $next = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 10 FROM clients')->fetchColumn();
                $pdo->prepare('INSERT INTO clients (name, logo, sort_order, is_active) VALUES (:name, :logo, :ord, :active)')
                    ->execute([':name' => $form['name'], ':logo' => $form['logo'], ':ord' => $next, ':active' => $form['is_active']]);
                flash($form['logo'] ? 'Cliente agregado.' : 'Cliente agregado sin logo: en el sitio se verá su nombre en texto.');
            }
            if ($oldLogo !== $form['logo']) {
                delete_uploaded_logo($oldLogo);
            }
            header('Location: ' . url('/admin/clientes.php'));
            exit;
        }
    } else {
        $client = find_client($pdo, $id);
        if ($client) {
            switch ($action) {
                case 'toggle':
                    $pdo->prepare('UPDATE clients SET is_active = 1 - is_active WHERE id = :id')->execute([':id' => $id]);
                    flash($client['is_active'] ? $client['name'] . ' ya no se muestra en el sitio.' : $client['name'] . ' vuelve a mostrarse en el sitio.');
                    break;
                case 'up':
                case 'down':
                    move_row($pdo, 'clients', $id, $action);
                    break;
                case 'delete':
                    $pdo->prepare('DELETE FROM clients WHERE id = :id')->execute([':id' => $id]);
                    delete_uploaded_logo($client['logo']);
                    flash($client['name'] . ' fue eliminado.');
                    break;
            }
        }
        header('Location: ' . url('/admin/clientes.php') . (in_array($action, ['up', 'down'], true) ? '#cliente-' . $id : ''));
        exit;
    }
}

$clients = $pdo->query('SELECT * FROM clients ORDER BY sort_order, id')->fetchAll();
$visible = count(array_filter($clients, fn($c) => $c['is_active']));
$teamSize = (int) ($pdo->query("SELECT value FROM settings WHERE name = 'team_size'")->fetchColumn() ?: TEAM_SIZE);
$activeBrands = (int) ($pdo->query("SELECT value FROM settings WHERE name = 'active_brands'")->fetchColumn() ?: ACTIVE_BRANDS);

$adminTitle = 'Clientes';
$adminSection = 'clients';
include __DIR__ . '/includes/layout-top.php';
$err = fn($k) => isset($errors[$k]) ? '<p class="error">' . e($errors[$k]) . '</p>' : '';
$last = count($clients) - 1;
?>
<div class="page-head">
    <div>
        <h1>Clientes</h1>
        <p class="muted"><?= count($clients) ?> clientes, <?= $visible ?> visibles. Aparecen en la franja del inicio y en la página Clientes, en este orden.</p>
    </div>
    <a class="btn btn--ghost" href="<?= e(url('/clientes')) ?>" target="_blank" rel="noopener">Ver página de clientes <?= icon('arrow-up') ?></a>
</div>

<?php if ($errors): ?>
    <div class="notice notice--error" role="alert">Revisa los campos marcados antes de guardar.</div>
<?php endif; ?>

<div class="split">
    <div class="split__main">
        <?php if ($clients): ?>
            <ol class="client-list">
                <?php foreach ($clients as $i => $c): $logo = public_file_url($c['logo']); ?>
                    <li class="client-row<?= $c['is_active'] ? '' : ' is-hidden' ?><?= (int) $c['id'] === $editId ? ' is-editing' : '' ?>" id="cliente-<?= (int) $c['id'] ?>">
                        <div class="client-row__order">
                            <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $c['id'] ?>"><input type="hidden" name="action" value="up">
                                <button type="submit" class="order-btn" aria-label="Subir a <?= e($c['name']) ?>"<?= $i === 0 ? ' disabled' : '' ?>>↑</button></form>
                            <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $c['id'] ?>"><input type="hidden" name="action" value="down">
                                <button type="submit" class="order-btn" aria-label="Bajar a <?= e($c['name']) ?>"<?= $i === $last ? ' disabled' : '' ?>>↓</button></form>
                        </div>
                        <div class="logo-tile">
                            <?php if ($logo): ?>
                                <img src="<?= e($logo) ?>" alt="" loading="lazy">
                            <?php else: ?>
                                <span><?= e($c['name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="client-row__info">
                            <strong><?= e($c['name']) ?></strong>
                            <?php if (!$c['is_active']): ?><span class="pill">Oculto</span><?php endif; ?>
                            <?php if (!$logo): ?><span class="pill pill--scheduled">Sin logo</span><?php endif; ?>
                        </div>
                        <div class="client-row__actions">
                            <a href="?editar=<?= (int) $c['id'] ?>#form-cliente">Editar</a>
                            <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $c['id'] ?>"><input type="hidden" name="action" value="toggle">
                                <button type="submit" class="linklike"><?= $c['is_active'] ? 'Ocultar' : 'Mostrar' ?></button></form>
                            <form method="post" data-confirm="¿Eliminar a <?= e($c['name']) ?>? Si solo dejó de ser cliente por un tiempo, mejor ocúltalo.">
                                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $c['id'] ?>"><input type="hidden" name="action" value="delete">
                                <button type="submit" class="linklike danger">Eliminar</button></form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php else: ?>
            <div class="empty">
                <h2>Aún no hay clientes</h2>
                <p class="muted">Agrega el primero con el formulario. Mientras la lista esté vacía, la franja de logos no se mostrará con marcas.</p>
            </div>
        <?php endif; ?>
    </div>

    <aside class="split__side">
        <form method="post" enctype="multipart/form-data" class="panel" id="form-cliente">
            <h2><?= $form['id'] ? 'Editar cliente' : 'Agregar cliente' ?></h2>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
            <label class="field">
                <span>Nombre de la marca</span>
                <input type="text" name="name" required maxlength="120" value="<?= e($form['name']) ?>" placeholder="Ej. Surtipulpas">
                <?= $err('name') ?>
            </label>
            <div class="logo-tile logo-tile--lg" data-logo-preview>
                <?php if ($current = public_file_url($form['logo'])): ?>
                    <img src="<?= e($current) ?>" alt="Logo actual">
                <?php else: ?>
                    <span>Vista previa del logo</span>
                <?php endif; ?>
            </div>
            <label class="field">
                <span>Logo <small class="muted">PNG o WEBP, máx. 1 MB</small></span>
                <input type="file" name="logo" accept="image/png,image/webp,image/jpeg" data-logo-input>
                <?= $err('logo') ?>
            </label>
            <p class="hint">Usa el logo en <strong>blanco con fondo transparente</strong>, idealmente cuadrado (500×500 px). En el sitio se muestra sobre fondo índigo, como en esta vista previa.</p>
            <?php if ($form['logo']): ?>
                <label class="check"><input type="checkbox" name="remove_logo" value="1"> Quitar el logo actual</label>
            <?php endif; ?>
            <label class="check"><input type="checkbox" name="is_active" value="1"<?= $form['is_active'] ? ' checked' : '' ?>> Mostrar en el sitio</label>
            <button class="btn btn--block" type="submit"><?= $form['id'] ? 'Guardar cambios' : 'Agregar cliente' ?></button>
            <?php if ($form['id']): ?><a class="cancel" href="<?= e(url('/admin/clientes.php')) ?>">Cancelar</a><?php endif; ?>
        </form>

        <form method="post" class="panel">
            <h2>Cifras del sitio</h2>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="figures">
            <label class="field">
                <span>Marcas activas</span>
                <input type="number" name="active_brands" min="1" max="9999" required value="<?= $activeBrands ?>">
                <small class="muted">«<?= $activeBrands ?> marcas activas hacen click con nosotros». Es independiente de cuántos logos muestres.</small>
            </label>
            <label class="field">
                <span>Especialistas en el equipo</span>
                <input type="number" name="team_size" min="1" max="9999" required value="<?= $teamSize ?>">
                <small class="muted">Aparece en el inicio y en Nosotros.</small>
            </label>
            <button class="btn btn--ghost btn--block" type="submit">Guardar cifras</button>
        </form>
    </aside>
</div>

<?php include __DIR__ . '/includes/layout-bottom.php'; ?>
