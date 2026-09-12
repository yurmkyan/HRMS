<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('dashboard.php');
}
verify_csrf();
$_SESSION = [];
session_destroy();
header('Location: ' . BASE_URL . '/login.php');
exit;
