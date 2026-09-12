<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$user = current_user();
$pageTitle = t('Мой профиль');

$stmt = $pdo->prepare(
    "SELECT u.*, d.name AS dept_name, r.name AS role_name FROM users u
     LEFT JOIN departments d ON d.id = u.department_id
     JOIN roles r ON r.id = u.role_id
     WHERE u.id = ?"
);
$stmt->execute([$user['id']]);
$me = $stmt->fetch();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $phone = trim($_POST['phone'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';

    if ($newPassword !== '') {
        if (!password_verify($currentPassword, $me['password'])) {
            $errors[] = t('Текущий пароль указан неверно.');
        } elseif (strlen($newPassword) < 6) {
            $errors[] = t('Новый пароль должен содержать минимум 6 символов.');
        }
    }

    if (!$errors) {
      $pdo->beginTransaction();
      try {
        $pdo->prepare('UPDATE users SET phone = ?, password = ? WHERE id = ?')->execute([
          $phone ?: null,
          $newPassword !== '' ? password_hash($newPassword, PASSWORD_BCRYPT) : $me['password'],
          $user['id'],
        ]);
        $pdo->commit();
      } catch (Throwable $exception) {
        $pdo->rollBack();
        $errors[] = t('Не удалось обновить профиль.');
      }
    }

    if (!$errors) {
        flash_set('success', t('Профиль обновлён.'));
        redirect('profile.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="panel" style="max-width:560px;">
  <div class="panel-head"><h3><?= e(t('Мои данные')) ?></h3></div>

  <?php if ($errors): ?>
    <div class="alert alert-danger"><?= implode('<br>', array_map('e', $errors)) ?></div>
  <?php endif; ?>

  <div style="margin-bottom:18px;font-size:14px;line-height:1.9;">
    <div><strong><?= e(t('ФИО')) ?>:</strong> <?= e($me['full_name']) ?></div>
    <div><strong><?= e(t('Email')) ?>:</strong> <?= e($me['email']) ?></div>
    <div><strong><?= e(t('Роль')) ?>:</strong> <?= e(role_label($me['role_name'])) ?></div>
    <div><strong><?= e(t('Отдел')) ?>:</strong> <?= e($me['dept_name'] ?: '—') ?></div>
    <div><strong><?= e(t('Должность')) ?>:</strong> <?= e($me['position'] ?: '—') ?></div>
    <div><strong><?= e(t('Дата приёма')) ?>:</strong> <?= format_date($me['hire_date']) ?></div>
  </div>

  <form method="post">
    <?= csrf_field() ?>
    <div class="field">
      <label><?= e(t('Телефон')) ?></label>
      <input type="text" name="phone" value="<?= e($me['phone'] ?? '') ?>">
    </div>
    <div class="field">
      <label><?= e(t('Текущий пароль')) ?></label>
      <input type="password" name="current_password" placeholder="<?= e(t('Требуется только для смены пароля')) ?>">
    </div>
    <div class="field">
      <label><?= e(t('Новый пароль')) ?></label>
      <input type="password" name="new_password" placeholder="<?= e(t('Оставьте пустым, чтобы не менять')) ?>">
    </div>
    <button class="btn btn-primary" type="submit"><?= e(t('Сохранить')) ?></button>
  </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
