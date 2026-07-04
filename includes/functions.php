<?php
/** Shared helpers: escaping, settings, CSRF, auth, uploads, audit, QR tokens. */
require_once __DIR__ . '/db.php';
function e(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function setting(string $key, string $default = ''): string {
    try { $stmt = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1'); $stmt->execute([$key]); return (string)($stmt->fetchColumn() ?: $default); }
    catch (Throwable $e) { return $default; }
}
function csrf_token(): string { if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); return $_SESSION['csrf_token']; }
function verify_csrf(): void { if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) { http_response_code(419); exit('Invalid CSRF token'); } }
function secure_session(): void {
    $config = require __DIR__ . '/../config/config.php';
    session_name($config['security']['session_name']); session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>!empty($_SERVER['HTTPS']),'httponly'=>true,'samesite'=>'Lax']); session_start();
    if (isset($_SESSION['last_seen']) && time() - $_SESSION['last_seen'] > (int)$config['security']['session_timeout']) { session_destroy(); header('Location: /login.php?timeout=1'); exit; }
    $_SESSION['last_seen'] = time();
}
function require_login(): void { if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; } }
function can(string $permission): bool { return ($_SESSION['user']['role'] ?? '') === 'super_admin' || in_array($permission, $_SESSION['user']['permissions'] ?? [], true); }
function require_permission(string $permission): void { if (!can($permission)) { http_response_code(403); exit('Forbidden'); } }
function audit(string $action, string $entity, ?int $entityId = null): void { try { $stmt=db()->prepare('INSERT INTO activity_logs(user_id,action,entity,entity_id,ip_address,user_agent) VALUES(?,?,?,?,?,?)'); $stmt->execute([$_SESSION['user']['id']??null,$action,$entity,$entityId,$_SERVER['REMOTE_ADDR']??'',substr($_SERVER['HTTP_USER_AGENT']??'',0,255)]); } catch (Throwable $e) {} }
function secure_token(): string { return strtoupper(bin2hex(random_bytes(8))); }
function upload_file(array $file, string $folder): ?string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    $config = require __DIR__ . '/../config/config.php';
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > $config['security']['upload_max_bytes']) throw new RuntimeException('Invalid upload size.');
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $config['security']['allowed_uploads'], true)) throw new RuntimeException('File type not allowed.');
    $name = secure_token() . '.' . $ext; $dir = __DIR__ . '/../uploads/' . trim($folder, '/'); if (!is_dir($dir)) mkdir($dir, 0755, true);
    $path = $dir . '/' . $name; if (!move_uploaded_file($file['tmp_name'], $path)) throw new RuntimeException('Upload failed.'); return 'uploads/' . trim($folder, '/') . '/' . $name;
}
function json_response(array $payload): void { header('Content-Type: application/json'); echo json_encode($payload); exit; }
