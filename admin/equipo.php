<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/uploads.php';
$user = require_login();
$pdo = admin_db();

const PHOTO_MAX_BYTES = 3 * 1024 * 1024; // 3 MB
const PHOTO_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

$pdo->exec('CREATE TABLE IF NOT EXISTS team (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    role VARCHAR(80) NULL,
    photo VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
// Bases creadas antes de existir el cargo
if (!$pdo->query("SHOW COLUMNS FROM team LIKE 'role'")->fetch()) {
    $pdo->exec('ALTER TABLE team ADD COLUMN role VARCHAR(80) NULL AFTER name');
}

function find_member(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM team WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

$errors = [];
$editId = (int) ($_GET['editar'] ?? 0);
$form = ['id' => 0, 'name' => '', 'role' => '', 'photo' => null, 'is_active' => 1];
if ($editId && ($found = find_member($pdo, $editId))) {
    $form = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash('La sesión expiró. Vuelve a intentarlo.', 'error');
        header('Location: ' . url('/admin/equipo.php'));
        exit;
    }
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $existing = $id ? find_member($pdo, $id) : null;
        $form = [
            'id'        => $existing ? $id : 0,
            'name'      => trim((string) ($_POST['name'] ?? '')),
            'role'      => trim((string) ($_POST['role'] ?? '')),
            'photo'     => $existing['photo'] ?? null,
            'is_active' => empty($_POST['is_active']) ? 0 : 1,
        ];
        $editId = $form['id'];
        if (mb_strlen($form['name']) < 2 || mb_strlen($form['name']) > 120) {
            $errors['name'] = 'Escribe el nombre de la persona (entre 2 y 120 caracteres).';
        }
        if (mb_strlen($form['role']) < 2 || mb_strlen($form['role']) > 80) {
            $errors['role'] = 'Escribe el cargo (entre 2 y 80 caracteres), por ejemplo «Estratega digital».';
        }
        $newPhoto = $errors ? null : upload_image($_FILES['photo'] ?? [], 'equipo', $form['name'], PHOTO_MAX_BYTES, PHOTO_TYPES, $errors, 'photo');
        if (!$errors && !$newPhoto && !$form['photo']) {
            $errors['photo'] = 'Sube la foto de la persona.';
        }

        if (!$errors) {
            $oldPhoto = $form['photo'];
            if ($newPhoto) {
                $form['photo'] = $newPhoto;
            }
            if ($existing) {
                $pdo->prepare('UPDATE team SET name = :name, role = :role, photo = :photo, is_active = :active WHERE id = :id')
                    ->execute([':name' => $form['name'], ':role' => $form['role'], ':photo' => $form['photo'], ':active' => $form['is_active'], ':id' => $id]);
                flash('Integrante actualizado.');
            } else {
                $next = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 10 FROM team')->fetchColumn();
                $pdo->prepare('INSERT INTO team (name, role, photo, sort_order, is_active) VALUES (:name, :role, :photo, :ord, :active)')
                    ->execute([':name' => $form['name'], ':role' => $form['role'], ':photo' => $form['photo'], ':ord' => $next, ':active' => $form['is_active']]);
                flash($form['name'] . ' fue agregado al equipo.');
            }
            if ($oldPhoto !== $form['photo']) {
                delete_upload($oldPhoto, 'equipo');
            }
            header('Location: ' . url('/admin/equipo.php'));
            exit;
        }
    } else {
        $member = find_member($pdo, $id);
        if ($member) {
            switch ($action) {
                case 'toggle':
                    $pdo->prepare('UPDATE team SET is_active = 1 - is_active WHERE id = :id')->execute([':id' => $id]);
                    flash($member['is_active'] ? $member['name'] . ' ya no se muestra en el sitio.' : $member['name'] . ' vuelve a mostrarse en el sitio.');
                    break;
                case 'up':
                case 'down':
                    move_row($pdo, 'team', $id, $action);
                    break;
                case 'delete':
                    $pdo->prepare('DELETE FROM team WHERE id = :id')->execute([':id' => $id]);
                    delete_upload($member['photo'], 'equipo');
                    flash($member['name'] . ' fue eliminado del equipo.');
                    break;
            }
        }
        header('Location: ' . url('/admin/equipo.php') . (in_array($action, ['up', 'down'], true) ? '#persona-' . $id : ''));
        exit;
    }
}

$members = $pdo->query('SELECT * FROM team ORDER BY sort_order, id')->fetchAll();
$visible = count(array_filter($members, fn($m) => $m['is_active']));

$adminTitle = 'Equipo';
$adminSection = 'team';
include __DIR__ . '/includes/layout-top.php';
$err = fn($k) => isset($errors[$k]) ? '<p class="error">' . e($errors[$k]) . '</p>' : '';
$last = count($members) - 1;
?>
<div class="page-head">
    <div>
        <h1>Equipo</h1>
        <p class="muted"><?= count($members) ?> personas, <?= $visible ?> visibles. Sus fotos y nombres pasan en carrusel en Nosotros («El equipo detrás de cada click»), en este orden. Desde 6 personas se mueven solas.</p>
    </div>
    <a class="btn btn--ghost" href="<?= e(url('/nosotros#team-title')) ?>" target="_blank" rel="noopener">Ver en Nosotros <?= icon('arrow-up') ?></a>
</div>

<?php if ($errors): ?>
    <div class="notice notice--error" role="alert">Revisa los campos marcados antes de guardar.</div>
<?php endif; ?>

<div class="split">
    <div class="split__main">
        <?php if ($members): ?>
            <ol class="client-list">
                <?php foreach ($members as $i => $m): $photo = public_file_url($m['photo']); ?>
                    <li class="client-row<?= $m['is_active'] ? '' : ' is-hidden' ?><?= (int) $m['id'] === $editId ? ' is-editing' : '' ?>" id="persona-<?= (int) $m['id'] ?>">
                        <div class="client-row__order">
                            <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $m['id'] ?>"><input type="hidden" name="action" value="up">
                                <button type="submit" class="order-btn" aria-label="Subir a <?= e($m['name']) ?>"<?= $i === 0 ? ' disabled' : '' ?>>↑</button></form>
                            <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $m['id'] ?>"><input type="hidden" name="action" value="down">
                                <button type="submit" class="order-btn" aria-label="Bajar a <?= e($m['name']) ?>"<?= $i === $last ? ' disabled' : '' ?>>↓</button></form>
                        </div>
                        <div class="photo-tile">
                            <?php if ($photo): ?><img src="<?= e($photo) ?>" alt="" loading="lazy"><?php endif; ?>
                        </div>
                        <div class="client-row__info">
                            <strong><?= e($m['name']) ?></strong>
                            <?php if (!empty($m['role'])): ?><span class="muted"><?= e($m['role']) ?></span><?php else: ?><span class="pill pill--scheduled">Sin cargo</span><?php endif; ?>
                            <?php if (!$m['is_active']): ?><span class="pill">Oculto</span><?php endif; ?>
                            <?php if (!$photo): ?><span class="pill pill--scheduled">Sin foto</span><?php endif; ?>
                        </div>
                        <div class="client-row__actions">
                            <a href="?editar=<?= (int) $m['id'] ?>#form-persona">Editar</a>
                            <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $m['id'] ?>"><input type="hidden" name="action" value="toggle">
                                <button type="submit" class="linklike"><?= $m['is_active'] ? 'Ocultar' : 'Mostrar' ?></button></form>
                            <form method="post" data-confirm="¿Eliminar a <?= e($m['name']) ?> del equipo? Su foto se borrará del servidor.">
                                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $m['id'] ?>"><input type="hidden" name="action" value="delete">
                                <button type="submit" class="linklike danger">Eliminar</button></form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php else: ?>
            <div class="empty">
                <h2>Aún no hay fotos del equipo</h2>
                <p class="muted">Agrega a cada integrante con su foto. Mientras no haya ninguna, Nosotros muestra solo el texto del equipo.</p>
            </div>
        <?php endif; ?>
    </div>

    <aside class="split__side">
        <form method="post" enctype="multipart/form-data" class="panel" id="form-persona">
            <h2><?= $form['id'] ? 'Editar integrante' : 'Agregar integrante' ?></h2>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
            <label class="field">
                <span>Nombre</span>
                <input type="text" name="name" required maxlength="120" value="<?= e($form['name']) ?>" placeholder="Ej. Ana Gómez">
                <small class="muted">Aparece debajo de la foto en el carrusel de Nosotros.</small>
                <?= $err('name') ?>
            </label>
            <label class="field">
                <span>Cargo</span>
                <input type="text" name="role" required maxlength="80" value="<?= e($form['role'] ?? '') ?>" placeholder="Ej. Estratega digital" list="roles-usados">
                <small class="muted">Se muestra debajo del nombre.</small>
                <?= $err('role') ?>
            </label>
            <datalist id="roles-usados">
                <?php foreach (array_unique(array_filter(array_column($members, 'role'))) as $r): ?><option value="<?= e($r) ?>"><?php endforeach; ?>
            </datalist>
            <div class="photo-tile photo-tile--lg" data-logo-preview>
                <?php if ($current = public_file_url($form['photo'])): ?>
                    <img src="<?= e($current) ?>" alt="Foto actual">
                <?php else: ?>
                    <span>Vista previa de la foto</span>
                <?php endif; ?>
            </div>
            <label class="field">
                <span>Foto <small class="muted">JPG, PNG o WEBP, máx. 3 MB</small></span>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" data-logo-input<?= $form['photo'] ? '' : ' required' ?>>
                <?= $err('photo') ?>
            </label>
            <p class="hint">Usa fotos verticales (ideal 800×1000 px) con el mismo estilo: mismo fondo, luz y encuadre de hombros hacia arriba. Así el bloque se ve como un solo equipo.</p>
            <label class="check"><input type="checkbox" name="is_active" value="1"<?= $form['is_active'] ? ' checked' : '' ?>> Mostrar en el sitio</label>
            <button class="btn btn--block" type="submit"><?= $form['id'] ? 'Guardar cambios' : 'Agregar integrante' ?></button>
            <?php if ($form['id']): ?><a class="cancel" href="<?= e(url('/admin/equipo.php')) ?>">Cancelar</a><?php endif; ?>
        </form>
        <p class="hint">La cifra «<?= team_size() ?> especialistas» se cambia en <a href="<?= e(url('/admin/clientes.php')) ?>">Clientes &gt; Cifras del sitio</a>.</p>
    </aside>
</div>

<?php include __DIR__ . '/includes/layout-bottom.php'; ?>
