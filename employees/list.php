<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user = current_user();
$pageTitle = t('Сотрудники');
$canManage = can_create_employee($user);
$canSeeActions = in_array($user['role'], ['admin', 'hr', 'manager'], true);

$search = trim($_GET['q'] ?? '');
$deptFilter = $_GET['department'] ?? '';

$sql = "SELECT u.*, r.name AS role_name, d.name AS dept_name
        FROM users u
        JOIN roles r ON r.id = u.role_id
        LEFT JOIN departments d ON d.id = u.department_id
        WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.position LIKE ?)";
    $like = "%$search%";
    array_push($params, $like, $like, $like);
}
if ($deptFilter !== '') {
    $sql .= " AND u.department_id = ?";
    $params[] = $deptFilter;
}
$sql .= " ORDER BY u.full_name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$employees = $stmt->fetchAll();

$departments = get_departments_list($pdo);

include __DIR__ . '/../includes/header.php';
?>

<div class="panel">
  <div class="panel-head">
    <h3><?= e(t('Список сотрудников')) ?> (<?= count($employees) ?>)</h3>
    <?php if ($canManage): ?>
      <a href="<?= BASE_URL ?>/employees/add.php" class="btn btn-primary btn-sm">+ <?= e(t('Добавить сотрудника')) ?></a>
    <?php endif; ?>
  </div>

  <form method="get" style="display:flex;gap:10px;margin-bottom:18px;">
    <input type="text" name="q" placeholder="<?= e(t('Поиск по имени, email, должности...')) ?>" value="<?= e($search) ?>" style="flex:1;padding:9px 12px;border:1px solid var(--border);border-radius:8px;">
    <select name="department" style="padding:9px 12px;border:1px solid var(--border);border-radius:8px;">
      <option value=""><?= e(t('Все отделы')) ?></option>
      <?php foreach ($departments as $d): ?>
        <option value="<?= $d['id'] ?>" <?= $deptFilter == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-outline" type="submit"><?= e(t('Найти')) ?></button>
  </form>

  <?php if (!$employees): ?>
    <div class="empty-state"><?= e(t('Сотрудники не найдены.')) ?></div>
  <?php else: ?>
  <table>
    <thead>
      <tr>
        <th><?= e(t('Имя')) ?></th><th><?= e(t('Должность')) ?></th><th><?= e(t('Отдел')) ?></th><th><?= e(t('Роль')) ?></th><th><?= e(t('Дата приёма')) ?></th><th><?= e(t('Статус')) ?></th>
        <?php if ($canSeeActions): ?><th><?= e(t('Действия')) ?></th><?php endif; ?>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($employees as $emp): ?>
      <tr>
        <td>
          <strong><?= e($emp['full_name']) ?></strong><br>
          <span style="color:var(--muted);font-size:12.5px;"><?= e($emp['email']) ?></span>
        </td>
        <td><?= e($emp['position'] ?: '—') ?></td>
        <td><?= e($emp['dept_name'] ?: '—') ?></td>
        <td><?= e(role_label($emp['role_name'])) ?></td>
        <td><?= format_date($emp['hire_date']) ?></td>
        <td><?= status_badge($emp['status']) ?></td>
        <?php if ($canSeeActions): ?>
        <td class="table-actions">
          <?php if (can_edit_employee($user, $emp)): ?>
            <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/employees/edit.php?id=<?= $emp['id'] ?>"><?= e(t('Изменить')) ?></a>
            <?php if ($emp['id'] != $user['id']): ?>
            <form method="post" action="<?= BASE_URL ?>/employees/delete.php" onsubmit="return confirm('Деактивировать сотрудника «<?= e($emp['full_name']) ?>»?');" style="display:inline;">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= $emp['id'] ?>">
              <button class="btn btn-danger btn-sm" type="submit"><?= e(t('Удалить')) ?></button>
            </form>
            <?php endif; ?>
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
