<?php
/** Expects $pageTitle to be set before include */
$user = current_user();
?>
<!DOCTYPE html>
<html lang="<?= current_language() ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'HRMS') ?> — <?= e(APP_NAME) ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="app">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <button class="menu-toggle" type="button" aria-label="Open navigation" aria-controls="main-sidebar" aria-expanded="false">☰</button>
      <h2><?= e($pageTitle ?? '') ?></h2>
      <div class="topbar-tools">
        <div class="language-switcher"><span><?= e(t('Язык')) ?>:</span> <a href="<?= e(language_url('hy')) ?>" class="<?= current_language() === 'hy' ? 'active' : '' ?>">Հայ</a> <a href="<?= e(language_url('ru')) ?>" class="<?= current_language() === 'ru' ? 'active' : '' ?>">Рус</a> <a href="<?= e(language_url('en')) ?>" class="<?= current_language() === 'en' ? 'active' : '' ?>">Eng</a></div>
      <div class="user-chip">
        <div class="avatar"><?= e(mb_substr($user['full_name'] ?? '?', 0, 1)) ?></div>
        <div>
          <div style="font-weight:600;color:var(--text)"><?= e($user['full_name'] ?? '') ?></div>
          <div><?= e(role_label($user['role'] ?? '')) ?></div>
        </div>
      </div>
      </div>
    </div>
    <div class="content">
      <?php foreach (flash_get() as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
      <?php endforeach; ?>
