<?php
/** Subida y borrado de imágenes del panel (logos de clientes y fotos del equipo). */

/**
 * Valida y guarda una imagen subida. Devuelve su ruta relativa a la raíz del sitio
 * (ej. uploads/equipo/ana-1a2b3c4d.jpg) o null si no se subió nada o hubo un error.
 *
 * @param string $folder Carpeta dentro de uploads/, ej. 'clientes'
 * @param array  $types  Tipos MIME permitidos => extensión
 */
function upload_image(array $file, string $folder, string $baseName, int $maxBytes, array $types, array &$errors, string $field): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[$field] = $file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE
            ? 'La imagen es demasiado pesada para el servidor. Comprímela antes de subirla.'
            : 'No se pudo subir la imagen. Inténtalo de nuevo.';
        return null;
    }
    if ($file['size'] > $maxBytes) {
        $errors[$field] = 'La imagen pesa más de ' . round($maxBytes / 1024 / 1024, 1) . ' MB. Comprímela antes de subirla.';
        return null;
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($types[$mime]) || !getimagesize($file['tmp_name'])) {
        $errors[$field] = 'Formato no permitido. Usa ' . strtoupper(implode(', ', array_unique($types))) . '.';
        return null;
    }
    $dir = __DIR__ . '/../../uploads/' . $folder . '/';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        $errors[$field] = 'No existe la carpeta uploads/' . $folder . ' y no se pudo crear.';
        return null;
    }
    $name = (slugify($baseName) ?: 'imagen') . '-' . bin2hex(random_bytes(4)) . '.' . $types[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
        $errors[$field] = 'No se pudo guardar la imagen en el servidor.';
        return null;
    }
    return 'uploads/' . $folder . '/' . $name;
}

/** Borra una imagen solo si fue subida desde el panel a uploads/<folder>/ (nunca archivos del sitio). */
function delete_upload(?string $rel, string $folder): void
{
    $prefix = 'uploads/' . $folder . '/';
    if ($rel && str_starts_with($rel, $prefix)) {
        $file = __DIR__ . '/../../' . $prefix . basename($rel);
        if (is_file($file)) {
            @unlink($file);
        }
    }
}

/** Mueve un registro una posición arriba o abajo y renumera sort_order de toda la tabla. */
function move_row(PDO $pdo, string $table, int $id, string $direction): void
{
    $ids = array_map('intval', $pdo->query("SELECT id FROM {$table} ORDER BY sort_order, id")->fetchAll(PDO::FETCH_COLUMN));
    $pos = array_search($id, $ids, true);
    if ($pos === false) {
        return;
    }
    $swap = $direction === 'up' ? $pos - 1 : $pos + 1;
    if (isset($ids[$swap])) {
        [$ids[$pos], $ids[$swap]] = [$ids[$swap], $ids[$pos]];
    }
    $stmt = $pdo->prepare("UPDATE {$table} SET sort_order = :ord WHERE id = :id");
    foreach ($ids as $i => $rowId) {
        $stmt->execute([':ord' => ($i + 1) * 10, ':id' => $rowId]);
    }
}
