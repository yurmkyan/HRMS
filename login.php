<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
  $captchaAnswer = $_POST['captcha'] ?? '';
  $honeypot = trim($_POST['website'] ?? '');

  if (login_rate_limited($email)) {
    $error = t('Слишком много попыток. Попробуйте позже.');
  } elseif ($honeypot !== '' || !verify_captcha($captchaAnswer)) {
    $error = t('Проверка безопасности не пройдена. Обновите код и попробуйте снова.');
  } elseif ($email === '' || $password === '') {
        $error = t('Введите email и пароль.');
    } else {
        $stmt = $pdo->prepare(
            'SELECT u.*, r.name AS role_name FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($password, $row['password'])) {
          login_rate_failure($email);
            $error = t('Неверный email или пароль.');
        } elseif ($row['status'] !== 'active') {
            $error = t('Учётная запись деактивирована. Обратитесь к администратору.');
        } else {
          login_rate_clear($email);
            $_SESSION['user'] = [
                'id' => $row['id'],
                'full_name' => $row['full_name'],
                'email' => $row['email'],
                'role' => $row['role_name'],
                'department_id' => $row['department_id'],
            ];
            redirect('dashboard.php');
        }
    }
}

  $captcha = captcha_code();
?>
<!DOCTYPE html>
<html lang="<?= current_language() ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e(t('Вход')) ?> — <?= e(APP_NAME) ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <h1>🧩 HRMS</h1>
    <div class="language-switcher" style="text-align:right;margin-bottom:12px;">
      <span><?= e(t('Язык')) ?>:</span>
      <a href="<?= e(language_url('hy')) ?>">Հայ</a>
      <a href="<?= e(language_url('ru')) ?>">Рус</a>
      <a href="<?= e(language_url('en')) ?>">Eng</a>
    </div>
    <p class="sub"><?= e(t('Система управления персоналом — вход в аккаунт')) ?></p>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
      <?= csrf_field() ?>
      <div class="field">
        <label>Email</label>
        <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
      </div>
      <div class="field">
        <label><?= e(t('Пароль')) ?></label>
        <input type="password" name="password" required>
      </div>
      <div class="captcha-box">
        <div class="captcha-label"><?= e(t('Введите код с картинки')) ?></div>
        <div class="captcha-code"><img src="<?= BASE_URL ?>/captcha.php?v=<?= time() ?>" alt="<?= e(t('Изображение проверки')) ?>"></div>
        <input type="text" name="captcha" maxlength="5" autocomplete="off" required aria-label="<?= e(t('Код безопасности')) ?>">
        <input class="captcha-trap" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
      </div>
      <button class="btn btn-primary btn-block" type="submit"><?= e(t('Войти')) ?></button>
    </form>

  </div>
</div>
</body>
</html>
