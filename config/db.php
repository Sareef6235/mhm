<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$dbHost = 'localhost';
$dbName = 'hvernued_range';
$dbUser = 'hvernued_cpses_hvnqmd5ph8';
$dbPass = 'Zirect@1618*1##';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Database connection failed. Please check config/db.php settings.');
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function require_admin(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function require_student(): void
{
    if (empty($_SESSION['student'])) {
        header('Location: login.php');
        exit;
    }
}
