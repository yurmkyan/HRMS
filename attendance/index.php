<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user = current_user();
$pageTitle = t('Посещаемость');
$canSeeAll = in_array($user['role'], ['admin', 'hr', 'manager'], true);
$today = date('Y-m-d');

// Handle check-in / check-out actions for the current user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM attendance WHERE user_id = ? AND work_date = ?');
    $stmt->execute([$user['id'], $today]);
    $row = $stmt->fetch();

    if ($action === 'check_in') {
        $now = date('H:i:s');
        $status = ($now > '09:15:00') ? 'late' : 'present';
        if (!$row) {
            $pdo->prepare('INSERT INTO attendance (user_id, work_date, check_in, status) VALUES (?, ?, ?, ?)')
                ->execute([$user['id'], $today, $now, $status]);
            flash_set('success', t('Вы отметились на приход в ') . substr($now, 0, 5) . '.');
        } else {
            flash_set('warning', t('Вы уже отмечались сегодня.'));
        }
    } elseif ($action === 'check_out') {
        if ($row && !$row['check_out']) {
            $pdo->prepare('UPDATE attendance SET check_out = ? WHERE id = ?')
                ->execute([date('H:i:s'), $row['id']]);
            flash_set('success', t('Уход зафиксирован.'));
        } else {
            flash_set('warning', t('Нет активной отметки прихода на сегодня.'));
        }
    }
    redirect('attendance/index.php');
}

$stmt = $pdo->prepare('SELECT * FROM attendance WHERE user_id = ? AND work_date = ?');
$stmt->execute([$user['id'], $today]);
$todayRow = $stmt->fetch() ?: null;

// Own recent history
$stmt = $pdo->prepare('SELECT * FROM attendance WHERE user_id = ? ORDER BY work_date DESC LIMIT 14');
$stmt->execute([$user['id']]);
$history = $stmt->fetchAll();

// Team-wide view for managers/hr/admin
$teamToday = [];
if ($canSeeAll) {
    $teamToday = $pdo->query(
        "SELECT a.*, u.full_name, u.position
         FROM attendance a JOIN users u ON u.id = a.user_id
         WHERE a.work_date = CURDATE()
         ORDER BY a.check_in"
    )->fetchAll();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="panel">
  <div class="panel-head"><h3><?= e(t('Сегодня')) ?> — <?= date('d.m.Y') ?></h3></div>
  <div style="display:flex;gap:14px;align-items:center;">
    <div>
      <div style="font-size:13px;color:var(--muted);"><?= e(t('Приход')) ?></div>
      <div style="font-size:20px;font-weight:700;"><?= !empty($todayRow['check_in']) ? substr($todayRow['check_in'],0,5) : '—' ?></div>
    </div>
    <div>
      <div style="font-size:13px;color:var(--muted);"><?= e(t('Уход')) ?></div>
      <div style="font-size:20px;font-weight:700;"><?= !empty($todayRow['check_out']) ? substr($todayRow['check_out'],0,5) : '—' ?></div>
    </div>
    <div style="flex:1;"></div>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="check_in">
      <button class="btn btn-primary" type="submit" <?= $todayRow ? 'disabled' : '' ?>><?= e(t('Отметить приход')) ?></button>
    </form>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="check_out">
      <button class="btn btn-coral" type="submit" <?= (!$todayRow || $todayRow['check_out']) ? 'disabled' : '' ?>><?= e(t('Отметить уход')) ?></button>
    </form>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h3><?= e(t('Моя история (последние 14 дней)')) ?></h3></div>
  <?php if (!$history): ?>
    <div class="empty-state"><?= e(t('Записей пока нет.')) ?></div>
  <?php else: ?>
  <table>
    <thead><tr><th><?= e(t('Дата')) ?></th><th><?= e(t('Приход')) ?></th><th><?= e(t('Уход')) ?></th><th><?= e(t('Статус')) ?></th></tr></thead>
    <tbody>
    <?php foreach ($history as $h): ?>
      <tr>
        <td><?= format_date($h['work_date']) ?></td>
        <td><?= $h['check_in'] ? substr($h['check_in'],0,5) : '—' ?></td>
        <td><?= $h['check_out'] ? substr($h['check_out'],0,5) : '—' ?></td>
        <td><?= status_badge($h['status']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<?php if ($canSeeAll): ?>
<div class="panel">
  <div class="panel-head"><h3><?= e(t('Команда сегодня')) ?></h3></div>
  <?php if (!$teamToday): ?>
    <div class="empty-state"><?= e(t('Пока никто не отметился.')) ?></div>
  <?php else: ?>
  <table>
    <thead><tr><th><?= e(t('Сотрудник')) ?></th><th><?= e(t('Должность')) ?></th><th><?= e(t('Приход')) ?></th><th><?= e(t('Уход')) ?></th><th><?= e(t('Статус')) ?></th></tr></thead>
    <tbody>
    <?php foreach ($teamToday as $t): ?>
      <tr>
        <td><?= e($t['full_name']) ?></td>
        <td><?= e($t['position'] ?: '—') ?></td>
        <td><?= $t['check_in'] ? substr($t['check_in'],0,5) : '—' ?></td>
        <td><?= $t['check_out'] ? substr($t['check_out'],0,5) : '—' ?></td>
        <td><?= status_badge($t['status']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
