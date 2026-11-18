<?php
require __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    logout();
}
header('Location: ' . url('/admin/login.php'));
exit;
