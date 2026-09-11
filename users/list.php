<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role(['admin']);

$pageTitle = t('Пользователи системы');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $roleId = (int)($_POST['role_id'] ?? 0);
    $me = current_user();
    if ($id === (int)$me['id']) {
        flash_set('danger', t('Нельзя изменить роль собственной учётной записи.'));
    } else {
        $pdo->prepare('UPDATE users SET role_id = ? WHERE id = ?')->execute([$roleId, $id]);
        flash_set('success', t('Роль пользователя обновлена.'));
    }
    redirect('users/list.php');
}

$users = $pdo->query(
    "SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.full_name"
)->fetchAll();
$roles = get_roles_list($pdo);

include __DIR__ . '/../includes/header.php';
?>

<div class="panel">
  <div class="panel-head"><h3><?= e(t('Учётные записи и роли')) ?> (<?= count($users) ?>)</h3></div>
  <table>
    <thead><tr><th><?= e(t('Имя')) ?></th><th>Email</th><th><?= e(t('Текущая роль')) ?></th><th><?= e(t('Статус')) ?></th><th><?= e(t('Изменить роль')) ?></th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?= e($u['full_name']) ?></td>
        <td><?= e($u['email']) ?></td>
        <td><?= e(role_label($u['role_name'])) ?></td>
        <td><?= status_badge($u['status']) ?></td>
        <td>
          <form method="post" style="display:flex;gap:8px;">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $u['id'] ?>">
            <select name="role_id" <?= $u['id'] == current_user()['id'] ? 'disabled' : '' ?>>
              <?php foreach ($roles as $r): ?>
                <option value="<?= $r['id'] ?>" <?= $u['role_id'] == $r['id'] ? 'selected' : '' ?>><?= e(role_label($r['name'])) ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-outline btn-sm" type="submit" <?= $u['id'] == current_user()['id'] ? 'disabled' : '' ?>><?= e(t('Сохранить')) ?></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
