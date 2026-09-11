<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('employees/list.php');
}
verify_csrf();

$id = (int)($_POST['id'] ?? 0);
$user = current_user();
$targetStmt = $pdo->prepare('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?');
$targetStmt->execute([$id]);
$target = $targetStmt->fetch();

if (!$target || !can_edit_employee($user, $target)) {
    http_response_code(403);
    die(t('Доступ запрещён.'));
}

if ($id === (int)$user['id']) {
    flash_set('danger', t('Вы не можете удалить собственную учётную запись.'));
    redirect('employees/list.php');
}

// Soft delete: mark inactive rather than physically removing the record,
// preserving attendance/payroll history.
$stmt = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
$stmt->execute([$id]);

flash_set('success', t('Сотрудник деактивирован.'));
redirect('employees/list.php');
