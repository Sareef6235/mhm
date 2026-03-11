<?php

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$dbHost = 'localhost';
$dbName = 'hvernued_range';
$dbUser = 'hvernued_cpses_hvnqmd5ph8';
$dbPass = 'Zirect@1618*1##';

$pdo = null;
$dbWarning = '';
$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // Development fallback to avoid HTTP 500 when MySQL is unavailable in local/container.
    $sqlitePath = __DIR__ . '/../database/dev.sqlite';
    $pdo = new PDO('sqlite:' . $sqlitePath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $dbWarning = 'MySQL connection failed; running on SQLite fallback for local debugging.';
}

function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
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
    return isset($_POST['csrf_token'], $_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token']);
}

function require_admin(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function ensure_tables(PDO $pdo): void
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $idDef = $driver === 'sqlite' ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
    $ignoreKeyword = $driver === 'sqlite' ? 'OR IGNORE' : 'IGNORE';

    $pdo->exec("CREATE TABLE IF NOT EXISTS admin (
        id {$idDef},
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id {$idDef},
        student_name VARCHAR(150) NOT NULL,
        class VARCHAR(10) NOT NULL,
        gender VARCHAR(10) NOT NULL,
        class_number INTEGER NOT NULL,
        created_at DATETIME NOT NULL,
        UNIQUE (class, gender, class_number)
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS textbooks (
        id {$idDef},
        book_name VARCHAR(150) NOT NULL,
        class VARCHAR(10) NOT NULL,
        price DECIMAL(10,2) NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS notebooks (
        id {$idDef},
        notebook_type VARCHAR(100) NOT NULL,
        pages INTEGER NOT NULL,
        price DECIMAL(10,2) NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id {$idDef},
        student_name VARCHAR(150) NOT NULL,
        class VARCHAR(10) NOT NULL,
        gender VARCHAR(10) NOT NULL,
        class_number INTEGER NOT NULL,
        item_type VARCHAR(20) NOT NULL,
        item_name VARCHAR(150) NOT NULL,
        pages INTEGER NULL,
        price DECIMAL(10,2) NOT NULL,
        quantity INTEGER NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");

    $hash = '$2y$12$k4jxSd9qC9Ct2pAIohB/pegFn1CyiEDMdRMvu/zwfjx0Sti7n/Mge';
    $stmt = $pdo->prepare("INSERT {$ignoreKeyword} INTO admin (username, password) VALUES (:u, :p)");
    $stmt->execute([':u' => 'admin', ':p' => $hash]);

    $countBooks = (int)$pdo->query('SELECT COUNT(*) FROM textbooks')->fetchColumn();
    if ($countBooks === 0) {
        $pdo->exec("INSERT INTO textbooks (book_name, class, price) VALUES
            ('Fiqh','1',90),('Arabic','1',80),('Nahvu','1',70),
            ('Fiqh','2',100),('Arabic','2',90),('Quran','2',120)");
    }

    $countNotebooks = (int)$pdo->query('SELECT COUNT(*) FROM notebooks')->fetchColumn();
    if ($countNotebooks === 0) {
        $pdo->exec("INSERT INTO notebooks (notebook_type, pages, price) VALUES
            ('Notebook',100,30),('Notebook',200,50),('Notebook',300,70)");
    }
}

function render_site_footer(): void
{
    echo '<footer class="site-footer"><p>Design by <a href="https://portfolio.example.com" target="_blank" rel="noopener noreferrer">Muhsin Faizy</a></p></footer>';
}

ensure_tables($pdo);
