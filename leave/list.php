<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user = current_user();
$pageTitle = t('Заявки на отпуск');
$canViewAll = in_array($user['role'], ['admin', 'hr', 'manager'], true);
$canReview = in_array($user['role'], ['admin', 'hr', 'manager'], true);

$scopeDepartmentId = in_array($user['role'], ['admin', 'hr'], true)
  ? null
  : (int)($user['department_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canReview) {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if (in_array($action, ['approved', 'rejected'], true)) {
      $check = $pdo->prepare(
        "SELECT lr.id FROM leave_requests lr
         JOIN users requester ON requester.id = lr.user_id
         WHERE lr.id = ? AND lr.status = 'pending'
         AND (? IS NULL OR requester.department_id = ?)"
      );
      $check->execute([$id, $scopeDepartmentId, $scopeDepartmentId]);
      if ($check->fetch()) {
        $pdo->prepare('UPDATE leave_requests SET status = ?, reviewed_by = ? WHERE id = ? AND status = "pending"')
          ->execute([$action, $user['id'], $id]);
        flash_set('success', 'Заявка обновлена.');
      } else {
        flash_set('warning', 'Заявка уже рассмотрена или недоступна.');
      }
    }
    redirect('leave/list.php');
}

if ($canViewAll) {
  $stmt = $pdo->prepare(
    "SELECT lr.*, u.full_name, lt.name AS type_name
         FROM leave_requests lr
         JOIN users u ON u.id = lr.user_id
         JOIN leave_types lt ON lt.id = lr.leave_type_id
     WHERE (? IS NULL OR u.department_id = ?)
     ORDER BY FIELD(lr.status,'pending','approved','rejected'), lr.created_at DESC"
  );
  $stmt->execute([$scopeDepartmentId, $scopeDepartmentId]);
  $requests = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare(
        "SELECT lr.*, u.full_name, lt.name AS type_name
         FROM leave_requests lr
         JOIN users u ON u.id = lr.user_id
         JOIN leave_types lt ON lt.id = lr.leave_type_id
         WHERE lr.user_id = ?
         ORDER BY lr.created_at DESC"
    );
    $stmt->execute([$user['id']]);
    $requests = $stmt->fetchAll();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="panel">
  <div class="panel-head">
    <h3><?= e(t('Заявки')) ?> (<?= count($requests) ?>)</h3>
    <a href="<?= BASE_URL ?>/leave/request.php" class="btn btn-primary btn-sm">+ <?= e(t('Новая заявка')) ?></a>
  </div>

  <?php if (!$requests): ?>
    <div class="empty-state"><?= e(t('Заявок пока нет.')) ?></div>
  <?php else: ?>
  <table>
    <thead>
      <tr>
        <?php if ($canReview): ?><th><?= e(t('Сотрудник')) ?></th><?php endif; ?>
        <th><?= e(t('Тип')) ?></th><th><?= e(t('Период')) ?></th><th><?= e(t('Причина')) ?></th><th><?= e(t('Статус')) ?></th>
        <?php if ($canReview): ?><th><?= e(t('Действия')) ?></th><?php endif; ?>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($requests as $r): ?>
      <tr>
        <?php if ($canReview): ?><td><?= e($r['full_name']) ?></td><?php endif; ?>
        <td><?= e(t($r['type_name'])) ?></td>
        <td><?= format_date($r['start_date']) ?> – <?= format_date($r['end_date']) ?></td>
        <td style="max-width:220px;color:var(--muted);"><?= e($r['reason'] ? t($r['reason']) : '—') ?></td>
        <td><?= status_badge($r['status']) ?></td>
        <?php if ($canReview): ?>
        <td class="table-actions">
          <?php if ($r['status'] === 'pending'): ?>
          <form method="post" style="display:inline;">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <input type="hidden" name="action" value="approved">
            <button class="btn btn-primary btn-sm" type="submit"><?= e(t('Одобрить')) ?></button>
          </form>
          <form method="post" style="display:inline;">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <input type="hidden" name="action" value="rejected">
            <button class="btn btn-danger btn-sm" type="submit"><?= e(t('Отклонить')) ?></button>
          </form>
          <?php else: ?>
            <span style="color:var(--muted);font-size:12.5px;"><?= e(t('Рассмотрено')) ?></span>
          <?php endif; ?>
        </td>
        <?php endif; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
