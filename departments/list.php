<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role(['admin', 'hr']);

$pageTitle = t('Отделы');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($name !== '') {
            $pdo->prepare('INSERT INTO departments (name, description) VALUES (?, ?)')->execute([$name, $desc ?: null]);
            flash_set('success', t('Отдел добавлен.'));
        } else {
            flash_set('danger', t('Укажите название отдела.'));
        }
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($id && $name !== '') {
            $pdo->prepare('UPDATE departments SET name=?, description=? WHERE id=?')->execute([$name, $desc ?: null, $id]);
            flash_set('success', t('Отдел обновлён.'));
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare('UPDATE users SET department_id = NULL WHERE department_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM departments WHERE id = ?')->execute([$id]);
        flash_set('success', t('Отдел удалён. Сотрудники этого отдела перенесены в «без отдела».'));
    }
    redirect('departments/list.php');
}

$departments = $pdo->query(
    "SELECT d.*, COUNT(u.id) AS emp_count
     FROM departments d
     LEFT JOIN users u ON u.department_id = d.id AND u.status = 'active'
     GROUP BY d.id ORDER BY d.name"
)->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="departments-grid">
  <div class="panel">
    <div class="panel-head"><h3><?= e(t('Список отделов')) ?> (<?= count($departments) ?>)</h3></div>
    <?php if (!$departments): ?>
      <div class="empty-state"><?= e(t('Отделы ещё не созданы.')) ?></div>
    <?php else: ?>
    <table>
      <thead><tr><th><?= e(t('Название')) ?></th><th><?= e(t('Описание')) ?></th><th><?= e(t('Сотрудников')) ?></th><th><?= e(t('Действия')) ?></th></tr></thead>
      <tbody>
      <?php foreach ($departments as $d): ?>
        <tr>
          <td><strong><?= e($d['name']) ?></strong></td>
          <td style="color:var(--muted);"><?= e($d['description'] ?: '—') ?></td>
          <td><?= $d['emp_count'] ?></td>
          <td class="table-actions">
            <button class="btn btn-outline btn-sm" type="button"
              onclick="document.getElementById('edit-<?= $d['id'] ?>').style.display='block';"><?= e(t('Изменить')) ?></button>
            <form method="post" onsubmit="return confirm('Удалить отдел «<?= e($d['name']) ?>»? Сотрудники останутся без отдела.');" style="display:inline;">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $d['id'] ?>">
              <button class="btn btn-danger btn-sm" type="submit"><?= e(t('Удалить')) ?></button>
            </form>
          </td>
        </tr>
        <tr id="edit-<?= $d['id'] ?>" style="display:none;background:#fafbff;">
          <td colspan="4">
            <form method="post" class="inline-form inline-form-end">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="id" value="<?= $d['id'] ?>">
              <div class="field" style="flex:1;margin:0;">
                <label><?= e(t('Название')) ?></label>
                <input type="text" name="name" value="<?= e($d['name']) ?>" required>
              </div>
              <div class="field" style="flex:2;margin:0;">
                <label><?= e(t('Описание')) ?></label>
                <input type="text" name="description" value="<?= e($d['description'] ?? '') ?>">
              </div>
              <button class="btn btn-primary" type="submit"><?= e(t('Сохранить')) ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <div class="panel">
    <div class="panel-head"><h3><?= e(t('Новый отдел')) ?></h3></div>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="create">
      <div class="field">
        <label><?= e(t('Название')) ?> *</label>
        <input type="text" name="name" required>
      </div>
      <div class="field">
        <label><?= e(t('Описание')) ?></label>
        <textarea name="description" rows="3"></textarea>
      </div>
      <button class="btn btn-primary btn-block" type="submit"><?= e(t('Добавить отдел')) ?></button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
