<?php
$user = current_user();
$role = $user['role'] ?? 'employee';
$current = basename($_SERVER['SCRIPT_NAME']);
function nav_active(string $file, string $current): string {
    return $current === $file ? 'active' : '';
}
?>
<div class="sidebar">
  <div class="brand">🧩 <span class="text">HR<span>MS</span></span></div>

  <a href="<?= BASE_URL ?>/dashboard.php" class="<?= nav_active('dashboard.php', $current) ?>">📊 <span class="label-text"><?= e(t('Дашборд')) ?></span></a>

  <div class="section-label label-text"><?= e(t('Кадры')) ?></div>
  <a href="<?= BASE_URL ?>/employees/list.php" class="<?= str_contains($_SERVER['SCRIPT_NAME'],'employees') ? 'active' : '' ?>">👤 <span class="label-text"><?= e(t('Сотрудники')) ?></span></a>
  <?php if (in_array($role, ['admin', 'hr'], true)): ?>
  <a href="<?= BASE_URL ?>/departments/list.php" class="<?= str_contains($_SERVER['SCRIPT_NAME'],'departments') ? 'active' : '' ?>">🏢 <span class="label-text"><?= e(t('Отделы')) ?></span></a>
  <?php endif; ?>

  <div class="section-label label-text"><?= e(t('Учёт времени')) ?></div>
  <a href="<?= BASE_URL ?>/attendance/index.php" class="<?= str_contains($_SERVER['SCRIPT_NAME'],'attendance') ? 'active' : '' ?>">🕒 <span class="label-text"><?= e(t('Посещаемость')) ?></span></a>
  <a href="<?= BASE_URL ?>/leave/list.php" class="<?= str_contains($_SERVER['SCRIPT_NAME'],'leave') ? 'active' : '' ?>">🏖️ <span class="label-text"><?= e(t('Отпуска')) ?></span></a>

  <?php if ($role === 'admin'): ?>
  <div class="section-label label-text"><?= e(t('Финансы')) ?></div>
  <a href="<?= BASE_URL ?>/payroll/list.php" class="<?= str_contains($_SERVER['SCRIPT_NAME'],'payroll') ? 'active' : '' ?>">💳 <span class="label-text"><?= e(t('Зарплата')) ?></span></a>
  <?php endif; ?>

  <?php if ($role === 'admin'): ?>
  <div class="section-label label-text"><?= e(t('Система')) ?></div>
  <a href="<?= BASE_URL ?>/users/list.php" class="<?= str_contains($_SERVER['SCRIPT_NAME'],'users') ? 'active' : '' ?>">⚙️ <span class="label-text"><?= e(t('Пользователи')) ?></span></a>
  <?php endif; ?>

  <div class="sidebar-account">
    <a href="<?= BASE_URL ?>/profile.php">🙍 <span class="label-text"><?= e(t('Мой профиль')) ?></span></a>
    <a href="<?= BASE_URL ?>/logout.php" class="logout">🚪 <span class="label-text"><?= e(t('Выйти')) ?></span></a>
  </div>
</div>
