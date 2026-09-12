<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$currentUser = current_user();
if (!can_create_employee($currentUser)) {
  http_response_code(403);
  die(t('Доступ запрещён.'));
}

$pageTitle = t('Добавить сотрудника');
$departments = get_departments_list($pdo);
$roles = get_roles_list($pdo);
$canManageSalary = in_array($currentUser['role'], ['admin', 'hr'], true);
$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $old = $_POST;

    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $roleId = (int)($_POST['role_id'] ?? 0);
    $departmentValue = $_POST['department_id'] ?? '';
    $departmentId = $departmentValue !== '' ? (int)$departmentValue : null;
    $position = trim($_POST['position'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $hireDate = $_POST['hire_date'] ?? null;
    $salaryValue = $_POST['salary'] ?? '';
    $salary = $canManageSalary && $salaryValue !== '' ? (float)$salaryValue : null;

    if ($fullName === '') $errors[] = t('Укажите ФИО.');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = t('Укажите корректный email.');
    if (strlen($password) < 6) $errors[] = t('Пароль должен содержать минимум 6 символов.');
    if (!$roleId) $errors[] = t('Выберите роль.');

    $roleCheck = $pdo->prepare('SELECT id, name FROM roles WHERE id = ?');
    $roleCheck->execute([$roleId]);
    $selectedRole = $roleCheck->fetch();
    if (!$selectedRole || ($currentUser['role'] !== 'admin' && $selectedRole['name'] !== 'employee')) {
      $errors[] = t('Недопустимая роль.');
    }

    if ($departmentId !== null) {
      $departmentCheck = $pdo->prepare('SELECT id FROM departments WHERE id = ?');
      $departmentCheck->execute([$departmentId]);
      if (!$departmentCheck->fetch()) $errors[] = t('Недопустимый отдел.');
    }

    if (!$errors) {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = t('Пользователь с таким email уже существует.');
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO users (full_name, email, password, role_id, department_id, position, phone, hire_date, salary, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "active")'
        );
        $stmt->execute([
            $fullName, $email, password_hash($password, PASSWORD_BCRYPT),
            $roleId, $departmentId, $position ?: null, $phone ?: null, $hireDate ?: null, $salary
        ]);
        flash_set('success', t('Сотрудник успешно добавлен.'));
        redirect('employees/list.php');
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="panel" style="max-width:720px;">
  <div class="panel-head"><h3><?= e(t('Новый сотрудник')) ?></h3></div>

  <?php if ($errors): ?>
    <div class="alert alert-danger"><?= implode('<br>', array_map('e', $errors)) ?></div>
  <?php endif; ?>

  <form method="post">
    <?= csrf_field() ?>
    <div class="form-grid">
      <div class="field">
        <label><?= e(t('ФИО')) ?> *</label>
        <input type="text" name="full_name" value="<?= e($old['full_name'] ?? '') ?>" required>
      </div>
      <div class="field">
        <label>Email *</label>
        <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" required>
      </div>
      <div class="field">
        <label><?= e(t('Пароль')) ?> *</label>
        <input type="password" name="password" required>
      </div>
      <div class="field">
        <label><?= e(t('Телефон')) ?></label>
        <input type="text" name="phone" value="<?= e($old['phone'] ?? '') ?>">
      </div>
      <div class="field">
        <label><?= e(t('Роль')) ?> *</label>
        <select name="role_id" required>
          <option value=""><?= e(t('— выбрать —')) ?></option>
          <?php foreach ($roles as $r): ?>
            <?php if ($currentUser['role'] !== 'admin' && $r['name'] !== 'employee') continue; ?>
            <option value="<?= $r['id'] ?>" <?= (($old['role_id'] ?? '') == $r['id']) ? 'selected' : '' ?>><?= e(role_label($r['name'])) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label><?= e(t('Отдел')) ?></label>
        <select name="department_id">
          <option value=""><?= e(t('— без отдела —')) ?></option>
          <?php foreach ($departments as $d): ?>
            <option value="<?= $d['id'] ?>" <?= (($old['department_id'] ?? '') == $d['id']) ? 'selected' : '' ?>><?= e($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label><?= e(t('Должность')) ?></label>
        <input type="text" name="position" value="<?= e($old['position'] ?? '') ?>">
      </div>
      <div class="field">
        <label><?= e(t('Дата приёма')) ?></label>
        <input type="date" name="hire_date" value="<?= e($old['hire_date'] ?? '') ?>">
      </div>
      <?php if ($canManageSalary): ?>
      <div class="field">
        <label><?= e(t('Оклад (֏)')) ?></label>
        <input type="number" step="0.01" name="salary" value="<?= e($old['salary'] ?? '') ?>">
      </div>
      <?php endif; ?>
    </div>
    <button class="btn btn-primary" type="submit"><?= e(t('Сохранить')) ?></button>
    <a class="btn btn-outline" href="<?= BASE_URL ?>/employees/list.php"><?= e(t('Отмена')) ?></a>
  </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
