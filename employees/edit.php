<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$employee = $stmt->fetch();

if (!$employee) {
    flash_set('danger', t('Сотрудник не найден.'));
    redirect('employees/list.php');
}

$employee['role_name'] = $pdo->query('SELECT name FROM roles WHERE id = ' . (int)$employee['role_id'])->fetchColumn();
$currentUser = current_user();
if (!can_edit_employee($currentUser, $employee)) {
  http_response_code(403);
  die(t('Доступ запрещён.'));
}

$pageTitle = t('Редактировать сотрудника');
$departments = get_departments_list($pdo);
$roles = get_roles_list($pdo);
$canManageSalary = can_manage_salary($currentUser, $employee);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $roleId = (int)($_POST['role_id'] ?? 0);
    $departmentId = $_POST['department_id'] !== '' ? (int)$_POST['department_id'] : null;
    $position = trim($_POST['position'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $hireDate = $_POST['hire_date'] ?? null;
    $salary = $canManageSalary ? (($_POST['salary'] ?? '') !== '' ? (float)$_POST['salary'] : null) : $employee['salary'];
    $status = $_POST['status'] ?? 'active';
    $newPassword = $_POST['password'] ?? '';

    if ($fullName === '') $errors[] = t('Укажите ФИО.');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = t('Укажите корректный email.');
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
    if ($currentUser['role'] !== 'admin') {
      $roleId = (int)$employee['role_id'];
      $status = $employee['status'];
      if ($currentUser['role'] === 'manager') $departmentId = $employee['department_id'];
    }

    if (!$errors) {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $check->execute([$email, $id]);
        if ($check->fetch()) $errors[] = t('Этот email уже занят другим пользователем.');
    }

    if (!$errors) {
        if ($newPassword !== '') {
            if (strlen($newPassword) < 6) {
                $errors[] = t('Новый пароль должен содержать минимум 6 символов.');
            } else {
                $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')
                    ->execute([password_hash($newPassword, PASSWORD_BCRYPT), $id]);
            }
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            'UPDATE users SET full_name=?, email=?, role_id=?, department_id=?, position=?, phone=?, hire_date=?, salary=?, status=?
             WHERE id=?'
        );
        $stmt->execute([$fullName, $email, $roleId, $departmentId, $position ?: null, $phone ?: null, $hireDate ?: null, $salary, $status, $id]);
        flash_set('success', t('Данные сотрудника обновлены.'));
        redirect('employees/list.php');
    } else {
        $employee = array_merge($employee, $_POST);
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="panel" style="max-width:720px;">
  <div class="panel-head"><h3><?= e(t('Редактирование: ')) ?><?= e($employee['full_name']) ?></h3></div>

  <?php if ($errors): ?>
    <div class="alert alert-danger"><?= implode('<br>', array_map('e', $errors)) ?></div>
  <?php endif; ?>

  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $employee['id'] ?>">
    <div class="form-grid">
      <div class="field">
        <label><?= e(t('ФИО')) ?> *</label>
        <input type="text" name="full_name" value="<?= e($employee['full_name']) ?>" required>
      </div>
      <div class="field">
        <label>Email *</label>
        <input type="email" name="email" value="<?= e($employee['email']) ?>" required>
      </div>
      <div class="field">
        <label><?= e(t('Новый пароль')) ?></label>
        <input type="password" name="password" placeholder="<?= e(t('Оставьте пустым, чтобы не менять')) ?>">
      </div>
      <div class="field">
        <label><?= e(t('Телефон')) ?></label>
        <input type="text" name="phone" value="<?= e($employee['phone'] ?? '') ?>">
      </div>
      <div class="field">
        <label><?= e(t('Роль')) ?> *</label>
        <select name="role_id" required>
          <?php foreach ($roles as $r): ?>
            <?php if ($currentUser['role'] !== 'admin' && $r['name'] !== 'employee') continue; ?>
            <option value="<?= $r['id'] ?>" <?= $employee['role_id'] == $r['id'] ? 'selected' : '' ?>><?= e(role_label($r['name'])) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label><?= e(t('Отдел')) ?></label>
        <select name="department_id">
          <option value=""><?= e(t('— без отдела —')) ?></option>
          <?php foreach ($departments as $d): ?>
            <option value="<?= $d['id'] ?>" <?= $employee['department_id'] == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label><?= e(t('Должность')) ?></label>
        <input type="text" name="position" value="<?= e($employee['position'] ?? '') ?>">
      </div>
      <div class="field">
        <label><?= e(t('Дата приёма')) ?></label>
        <input type="date" name="hire_date" value="<?= e($employee['hire_date'] ?? '') ?>">
      </div>
      <?php if ($canManageSalary): ?>
      <div class="field">
        <label><?= e(t('Оклад (֏)')) ?></label>
        <input type="number" step="0.01" name="salary" value="<?= e($employee['salary'] ?? '') ?>">
      </div>
      <?php endif; ?>
      <div class="field">
        <label><?= e(t('Статус')) ?></label>
        <select name="status">
          <option value="active" <?= $employee['status'] === 'active' ? 'selected' : '' ?>><?= e(t('Активен')) ?></option>
          <option value="inactive" <?= $employee['status'] === 'inactive' ? 'selected' : '' ?>><?= e(t('Неактивен')) ?></option>
        </select>
      </div>
    </div>
    <button class="btn btn-primary" type="submit"><?= e(t('Сохранить изменения')) ?></button>
    <a class="btn btn-outline" href="<?= BASE_URL ?>/employees/list.php"><?= e(t('Отмена')) ?></a>
  </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
