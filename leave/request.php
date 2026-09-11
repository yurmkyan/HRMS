<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user = current_user();
$pageTitle = t('Новая заявка на отпуск');
$leaveTypes = $pdo->query('SELECT * FROM leave_types ORDER BY name')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $leaveTypeId = (int)($_POST['leave_type_id'] ?? 0);
    $start = $_POST['start_date'] ?? '';
    $end = $_POST['end_date'] ?? '';
    $reason = trim($_POST['reason'] ?? '');

    if (!$leaveTypeId) $errors[] = t('Выберите тип отпуска.');
    if (!$start || !$end) $errors[] = t('Укажите даты начала и окончания.');
    if ($start && $end && $start > $end) $errors[] = t('Дата окончания не может быть раньше даты начала.');

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO leave_requests (user_id, leave_type_id, start_date, end_date, reason, status)
             VALUES (?, ?, ?, ?, ?, "pending")'
        );
        $stmt->execute([$user['id'], $leaveTypeId, $start, $end, $reason ?: null]);
        flash_set('success', t('Заявка отправлена на рассмотрение.'));
        redirect('leave/list.php');
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="panel" style="max-width:600px;">
  <div class="panel-head"><h3><?= e(t('Новая заявка')) ?></h3></div>

  <?php if ($errors): ?>
    <div class="alert alert-danger"><?= implode('<br>', array_map('e', $errors)) ?></div>
  <?php endif; ?>

  <form method="post">
    <?= csrf_field() ?>
    <div class="field">
      <label><?= e(t('Тип отпуска')) ?> *</label>
      <select name="leave_type_id" required>
        <option value=""><?= e(t('— выбрать —')) ?></option>
        <?php foreach ($leaveTypes as $t): ?>
          <option value="<?= $t['id'] ?>"><?= e(t($t['name'])) ?> (<?= $t['days_per_year'] ?> <?= e(t('дн./год')) ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-grid">
      <div class="field">
        <label><?= e(t('Дата начала')) ?> *</label>
        <input type="date" name="start_date" required>
      </div>
      <div class="field">
        <label><?= e(t('Дата окончания')) ?> *</label>
        <input type="date" name="end_date" required>
      </div>
    </div>
    <div class="field">
      <label><?= e(t('Причина')) ?></label>
      <textarea name="reason" rows="3" placeholder="<?= e(t('Необязательно')) ?>"></textarea>
    </div>
    <button class="btn btn-primary" type="submit"><?= e(t('Отправить заявку')) ?></button>
    <a class="btn btn-outline" href="<?= BASE_URL ?>/leave/list.php"><?= e(t('Отмена')) ?></a>
  </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
