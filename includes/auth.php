<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
    return isset($_SESSION['user']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Restrict a page to specific role names, e.g. require_role(['admin','hr'])
 */
function require_role(array $roles): void {
    require_login();
    $user = current_user();
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        die('<div style="font-family:sans-serif;padding:40px;text-align:center">
                <h2>403 — ' . e(t('Доступ запрещён')) . '</h2>
                <p>' . e(t('У вас недостаточно прав для просмотра этой страницы.')) . '</p>
                <a href="' . BASE_URL . '/dashboard.php">' . e(t('Вернуться на панель')) . '</a>
             </div>');
    }
}

function can_edit_employee(array $actor, array $employee): bool {
    if ($actor['role'] === 'admin') {
        return (int)$actor['id'] !== (int)$employee['id'];
    }

    if ((int)$actor['id'] === (int)$employee['id'] || $employee['role_name'] !== 'employee') {
        return false;
    }

    return $actor['role'] === 'hr'
        || ($actor['role'] === 'manager'
            && !empty($actor['department_id'])
            && (int)$actor['department_id'] === (int)$employee['department_id']);
}

function can_create_employee(array $actor): bool {
    return in_array($actor['role'], ['admin', 'hr'], true);
}

function can_manage_salary(array $actor, array $employee): bool {
    if ($actor['role'] === 'admin') {
        return true;
    }

    return $actor['role'] === 'hr' && ($employee['role_name'] ?? '') === 'employee';
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(400);
        die(e(t('Неверный CSRF токен. Обновите страницу и попробуйте снова.')));
    }
}

function flash_set(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_get(): array {
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}
