<?php

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
date_default_timezone_set('Asia/Manila');

ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']),
]);
session_start();

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

spl_autoload_register(function (string $class): void {
    foreach ([__DIR__, dirname(__DIR__) . '/models'] as $dir) {
        $file = $dir . '/' . $class . '.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

require_once __DIR__ . '/helpers.php';

set_exception_handler(function (Throwable $e): void {
    error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    render('error', [
        'code' => 500,
        'message' => 'Something went wrong while processing your request. Please try again later.',
        'pageTitle' => 'Error',
    ]);
});

$pdo = (new Database())->connect();
$lookup = new Lookup($pdo);
