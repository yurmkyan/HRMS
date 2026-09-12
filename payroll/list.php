<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role(['admin']);

$pageTitle = t('Зарплата');
$months = [1=>t('Январь'),2=>t('Февраль'),3=>t('Март'),4=>t('Апрель'),5=>t('Май'),6=>t('Июнь'),7=>t('Июль'),8=>t('Август'),9=>t('Сентябрь'),10=>t('Октябрь'),11=>t('Ноябрь'),12=>t('Декабрь')];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'generate') {
        $month = (int)$_POST['month'];
        $year = (int)$_POST['year'];

      if ($month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
        flash_set('danger', 'Укажите корректный месяц и год.');
        redirect('payroll/list.php');
      }

        $employees = $pdo->query("SELECT id, salary FROM users WHERE status='active'")->fetchAll();
        $inserted = 0;
        foreach ($employees as $emp) {
            $exists = $pdo->prepare('SELECT id FROM payroll WHERE user_id=? AND period_month=? AND period_year=?');
            $exists->execute([$emp['id'], $month, $year]);
            if ($exists->fetch()) continue;

            $base = (float)($emp['salary'] ?? 0);
            $pdo->prepare(
                'INSERT INTO payroll (user_id, period_month, period_year, base_salary, bonus, deductions, total)
                 VALUES (?, ?, ?, ?, 0, 0, ?)'
            )->execute([$emp['id'], $month, $year, $base, $base]);
            $inserted++;
        }
        flash_set('success', "Начислено записей: $inserted (уже существующие пропущены).");
    } elseif ($action === 'adjust') {
        $id = (int)$_POST['id'];
      $bonus = max(0, (float)($_POST['bonus'] ?? 0));
        $row = $pdo->prepare('SELECT base_salary FROM payroll WHERE id=?');
        $row->execute([$id]);
      $payroll = $row->fetch();
      if (!$payroll) {
        flash_set('danger', 'Начисление не найдено.');
        redirect('payroll/list.php');
      }
      $base = (float)$payroll['base_salary'];
        $deductions = min(max(0, (float)($_POST['deductions'] ?? 0)), $base + $bonus);
        $total = $base + $bonus - $deductions;
        $pdo->prepare('UPDATE payroll SET bonus=?, deductions=?, total=? WHERE id=?')
            ->execute([$bonus, $deductions, $total, $id]);
        flash_set('success', 'Начисление обновлено.');
    }
    redirect('payroll/list.php');
}

$month = (int)($_GET['month'] ?? date('n'));
$year = (int)($_GET['year'] ?? date('Y'));
if ($month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
  $month = (int)date('n');
  $year = (int)date('Y');
}

$stmt = $pdo->prepare(
    "SELECT p.*, u.full_name FROM payroll p
     JOIN users u ON u.id = p.user_id
     WHERE p.period_month = ? AND p.period_year = ?
     ORDER BY u.full_name"
);
$stmt->execute([$month, $year]);
$rows = $stmt->fetchAll();
$totalPayout = array_sum(array_column($rows, 'total'));

include __DIR__ . '/../includes/header.php';
?>

<div class="panel">
  <div class="panel-head"><h3><?= e(t('Начислить зарплату за период')) ?></h3></div>
  <form method="post" style="display:flex;gap:10px;align-items:flex-end;">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="generate">
    <div class="field" style="margin:0;">
      <label><?= e(t('Месяц')) ?></label>
      <select name="month">
        <?php foreach ($months as $num => $name): ?>
          <option value="<?= $num ?>" <?= $num == $month ? 'selected' : '' ?>><?= $name ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field" style="margin:0;">
      <label><?= e(t('Год')) ?></label>
      <input type="number" name="year" value="<?= $year ?>" style="width:100px;">
    </div>
    <button class="btn btn-primary" type="submit"><?= e(t('Начислить всем активным сотрудникам')) ?></button>
  </form>
</div>

<div class="panel">
  <div class="panel-head">
    <h3><?= $months[$month] ?> <?= $year ?> — начисления (<?= count($rows) ?>)</h3>
    <form method="get" style="display:flex;gap:8px;">
      <select name="month" onchange="this.form.submit()">
        <?php foreach ($months as $num => $name): ?>
          <option value="<?= $num ?>" <?= $num == $month ? 'selected' : '' ?>><?= $name ?></option>
        <?php endforeach; ?>
      </select>
      <input type="number" name="year" value="<?= $year ?>" style="width:90px;" onchange="this.form.submit()">
    </form>
  </div>

  <?php if (!$rows): ?>
    <div class="empty-state"><?= e(t('Начислений за этот период ещё нет.')) ?></div>
  <?php else: ?>
  <table>
    <thead><tr><th><?= e(t('Сотрудник')) ?></th><th><?= e(t('Оклад')) ?></th><th><?= e(t('Бонус')) ?></th><th><?= e(t('Удержания')) ?></th><th><?= e(t('Итого')) ?></th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= e($r['full_name']) ?></td>
        <td><?= format_money($r['base_salary']) ?></td>
        <td><?= format_money($r['bonus']) ?></td>
        <td><?= format_money($r['deductions']) ?></td>
        <td><strong><?= format_money($r['total']) ?></strong></td>
        <td>
          <button class="btn btn-outline btn-sm" type="button"
            onclick="document.getElementById('adj-<?= $r['id'] ?>').style.display='table-row';"><?= e(t('Корректировать')) ?></button>
        </td>
      </tr>
      <tr id="adj-<?= $r['id'] ?>" style="display:none;background:#fafbff;">
        <td colspan="6">
          <form method="post" style="display:flex;gap:10px;align-items:flex-end;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="adjust">
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <div class="field" style="margin:0;">
              <label><?= e(t('Бонус')) ?></label>
              <input type="number" step="0.01" name="bonus" value="<?= $r['bonus'] ?>">
            </div>
            <div class="field" style="margin:0;">
              <label><?= e(t('Удержания')) ?></label>
              <input type="number" step="0.01" name="deductions" value="<?= $r['deductions'] ?>">
            </div>
            <button class="btn btn-primary" type="submit"><?= e(t('Сохранить')) ?></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr><td colspan="4" style="text-align:right;font-weight:700;"><?= e(t('Итого к выплате:')) ?></td><td colspan="2" style="font-weight:800;color:var(--indigo-dark);"><?= format_money($totalPayout) ?></td></tr>
    </tfoot>
  </table>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
