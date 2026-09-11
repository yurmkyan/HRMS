<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$user = current_user();
$pageTitle = t('Дашборд');

$totalEmployees = (int)$pdo->query("SELECT COUNT(*) c FROM users WHERE status='active'")->fetch()['c'];
$totalDepartments = (int)$pdo->query("SELECT COUNT(*) c FROM departments")->fetch()['c'];
$pendingLeaves = (int)$pdo->query("SELECT COUNT(*) c FROM leave_requests WHERE status='pending'")->fetch()['c'];
$todayPresent = (int)$pdo->query("SELECT COUNT(*) c FROM attendance WHERE work_date = CURDATE() AND status IN ('present','late')")->fetch()['c'];

// Recent leave requests (scoped to own for employees, all for hr/admin/manager)
if (in_array($user['role'], ['admin', 'hr', 'manager'], true)) {
    $recentLeaves = $pdo->query(
        "SELECT lr.*, u.full_name, lt.name AS type_name
         FROM leave_requests lr
         JOIN users u ON u.id = lr.user_id
         JOIN leave_types lt ON lt.id = lr.leave_type_id
         ORDER BY lr.created_at DESC LIMIT 6"
    )->fetchAll();
} else {
    $stmt = $pdo->prepare(
        "SELECT lr.*, u.full_name, lt.name AS type_name
         FROM leave_requests lr
         JOIN users u ON u.id = lr.user_id
         JOIN leave_types lt ON lt.id = lr.leave_type_id
         WHERE lr.user_id = ?
         ORDER BY lr.created_at DESC LIMIT 6"
    );
    $stmt->execute([$user['id']]);
    $recentLeaves = $stmt->fetchAll();
}

// Department distribution for a simple bar list
$deptDist = $pdo->query(
    "SELECT d.name, COUNT(u.id) cnt
     FROM departments d
     LEFT JOIN users u ON u.department_id = d.id AND u.status='active'
     GROUP BY d.id ORDER BY cnt DESC"
)->fetchAll();
$maxCnt = max(1, ...array_map(fn($r) => (int)$r['cnt'], $deptDist ?: [['cnt'=>0]]));

include __DIR__ . '/includes/header.php';
?>

<div class="cards-row">
  <div class="stat-card">
    <div class="label"><?= e(t('Сотрудников')) ?></div>
    <div class="value"><?= $totalEmployees ?></div>
  </div>
  <div class="stat-card">
    <div class="label"><?= e(t('Отделов')) ?></div>
    <div class="value"><?= $totalDepartments ?></div>
  </div>
  <div class="stat-card">
    <div class="label"><?= e(t('Заявок на отпуск')) ?></div>
    <div class="value coral"><?= $pendingLeaves ?></div>
  </div>
  <div class="stat-card">
    <div class="label"><?= e(t('На месте сегодня')) ?></div>
    <div class="value"><?= $todayPresent ?></div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1.3fr 1fr;gap:20px;">
  <div class="panel">
    <div class="panel-head">
      <h3><?= e(t('Последние заявки на отпуск')) ?></h3>
      <a href="<?= BASE_URL ?>/leave/list.php" class="btn btn-outline btn-sm"><?= e(t('Все заявки')) ?></a>
    </div>
    <?php if (!$recentLeaves): ?>
      <div class="empty-state"><?= e(t('Заявок пока нет.')) ?></div>
    <?php else: ?>
    <table>
      <thead><tr><th><?= e(t('Сотрудник')) ?></th><th><?= e(t('Тип')) ?></th><th><?= e(t('Период')) ?></th><th><?= e(t('Статус')) ?></th></tr></thead>
      <tbody>
      <?php foreach ($recentLeaves as $lv): ?>
        <tr>
          <td><?= e($lv['full_name']) ?></td>
          <td><?= e($lv['type_name']) ?></td>
          <td><?= format_date($lv['start_date']) ?> – <?= format_date($lv['end_date']) ?></td>
          <td><?= status_badge($lv['status']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <div class="panel">
    <div class="panel-head"><h3><?= e(t('Сотрудники по отделам')) ?></h3></div>
    <?php if (!$deptDist): ?>
      <div class="empty-state"><?= e(t('Отделы ещё не созданы.')) ?></div>
    <?php else: foreach ($deptDist as $d): $pct = round(($d['cnt'] / $maxCnt) * 100); ?>
      <div style="margin-bottom:12px;">
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
          <span><?= e($d['name']) ?></span><strong><?= $d['cnt'] ?></strong>
        </div>
        <div style="background:#eef0fb;border-radius:6px;height:8px;overflow:hidden;">
          <div style="width:<?= $pct ?>%;background:var(--indigo);height:100%;"></div>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
