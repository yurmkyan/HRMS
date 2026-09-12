<?php
// ==========================================================
// HRMS Configuration
// Edit these values to match your local/server environment.
// ==========================================================

define('DB_HOST', getenv('HRMS_DB_HOST') ?: '127.0.1.19');
define('DB_NAME', getenv('HRMS_DB_NAME') ?: 'hrms_db');
define('DB_USER', getenv('HRMS_DB_USER') ?: 'root');
define('DB_PASS', getenv('HRMS_DB_PASS') ?: '');

define('APP_NAME', 'HRMS');
define('BASE_URL', ''); // e.g. '/hrms' if installed in a subfolder, otherwise leave empty

// Set HRMS_DEBUG=1 only for local development.
define('APP_DEBUG', filter_var(getenv('HRMS_DEBUG') ?: '0', FILTER_VALIDATE_BOOLEAN));

if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

date_default_timezone_set('Asia/Yerevan');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . ($e->getMessage()));
}
