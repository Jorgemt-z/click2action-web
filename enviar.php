<?php
/**
 * Procesa el formulario de contacto.
 * - Responde JSON cuando se envía con JavaScript (fetch).
 * - Redirige a /contacto con mensaje cuando el navegador no tiene JavaScript.
 * - Guarda el mensaje en la tabla `leads` (si hay base de datos) y lo envía por correo.
 */
require __DIR__ . '/includes/bootstrap.php';

$wantsJson = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch'
    || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

function respond(bool $ok, string $message, array $errors = [], array $old = []): void
{
    global $wantsJson;
    if ($wantsJson) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($ok ? 200 : 422);
        echo json_encode(['ok' => $ok, 'message' => $message, 'errors' => $errors], JSON_UNESCAPED_UNICODE);
    } else {
        $_SESSION['contact_flash'] = ['ok' => $ok, 'message' => $message, 'errors' => $errors, 'old' => $ok ? [] : $old];
        header('Location: ' . url('/contacto') . '#contenido', true, 303);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('/contacto'), true, 303);
    exit;
}

// Anti-spam: campo trampa y CSRF
if (!empty($_POST['website'])) {
    respond(true, '¡Gracias! Recibimos tu mensaje.');
}
if (!csrf_check($_POST['csrf'] ?? null)) {
    respond(false, 'Tu sesión expiró. Recarga la página e inténtalo de nuevo.');
}

// Límite: un envío cada 30 segundos por sesión
if (!empty($_SESSION['last_contact']) && time() - $_SESSION['last_contact'] < 30) {
    respond(false, 'Ya recibimos un mensaje hace un momento. Espera unos segundos antes de enviar otro.');
}

$data = [
    'nombre'   => trim((string) ($_POST['nombre'] ?? '')),
    'empresa'  => trim((string) ($_POST['empresa'] ?? '')),
    'email'    => trim((string) ($_POST['email'] ?? '')),
    'telefono' => trim((string) ($_POST['telefono'] ?? '')),
    'motivo'   => trim((string) ($_POST['motivo'] ?? '')),
    'mensaje'  => trim((string) ($_POST['mensaje'] ?? '')),
];

$errors = [];
if (mb_strlen($data['nombre']) < 3) {
    $errors['nombre'] = 'Escribe tu nombre completo.';
}
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Escribe un correo válido, por ejemplo nombre@empresa.com.';
}
if (strlen(preg_replace('/\D/', '', $data['telefono'])) < 7) {
    $errors['telefono'] = 'Escribe un número de celular de al menos 7 dígitos.';
}
if ($data['motivo'] === '') {
    $errors['motivo'] = 'Elige el motivo de tu mensaje.';
}
if (mb_strlen($data['mensaje']) < 10) {
    $errors['mensaje'] = 'Cuéntanos un poco más (mínimo 10 caracteres).';
}
if (empty($_POST['acepto'])) {
    $errors['acepto'] = 'Debes aceptar la política de tratamiento de datos para enviar el formulario.';
}
foreach (['nombre' => 120, 'empresa' => 120, 'email' => 160, 'telefono' => 20, 'motivo' => 120, 'mensaje' => 3000] as $k => $max) {
    if (mb_strlen($data[$k]) > $max) {
        $errors[$k] = 'Este campo es demasiado largo.';
    }
}

if ($errors) {
    respond(false, 'Revisa los campos marcados en rojo.', $errors, $data);
}

// 1) Guardar en la base de datos
$saved = false;
if ($pdo = db()) {
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO leads (name, company, email, phone, reason, message, ip, user_agent)
             VALUES (:n, :c, :e, :p, :r, :m, :ip, :ua)'
        );
        $stmt->execute([
            ':n' => $data['nombre'], ':c' => $data['empresa'], ':e' => $data['email'],
            ':p' => $data['telefono'], ':r' => $data['motivo'], ':m' => $data['mensaje'],
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? '', ':ua' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
        $saved = true;
    } catch (PDOException $e) {
        error_log('[Click 2 Action] No se pudo guardar el lead: ' . $e->getMessage());
    }
}

// 2) Enviar por correo
$clean = fn($v) => str_replace(["\r", "\n"], ' ', $v);
$subject = '=?UTF-8?B?' . base64_encode('Nuevo contacto web: ' . $clean($data['motivo'])) . '?=';
$body  = "Nuevo mensaje desde el formulario de click2action.com.co\n\n";
$body .= "Nombre:   {$data['nombre']}\n";
$body .= "Empresa:  " . ($data['empresa'] ?: '—') . "\n";
$body .= "Correo:   {$data['email']}\n";
$body .= "Celular:  {$data['telefono']}\n";
$body .= "Motivo:   {$data['motivo']}\n\n";
$body .= "Mensaje:\n{$data['mensaje']}\n\n";
$body .= "Fecha: " . date('Y-m-d H:i') . "\n";
$headers = [
    'From: ' . SITE_NAME . ' <' . FORM_FROM_EMAIL . '>',
    'Reply-To: ' . $clean($data['nombre']) . ' <' . $clean($data['email']) . '>',
    'Content-Type: text/plain; charset=UTF-8',
    'X-Mailer: PHP/' . PHP_VERSION,
];
$mailed = @mail(FORM_TO_EMAIL, $subject, $body, implode("\r\n", $headers));

if (!$saved && !$mailed) {
    respond(false, 'No pudimos enviar tu mensaje en este momento. Escríbenos por WhatsApp y te atendemos de inmediato.', [], $data);
}

$_SESSION['last_contact'] = time();
respond(true, '¡Gracias, ' . explode(' ', $data['nombre'])[0] . '! Recibimos tu mensaje y te contactaremos en menos de 24 horas hábiles.');
