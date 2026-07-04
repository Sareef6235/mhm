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

/** Enterprise member file-storage helpers. */
function member_storage_categories(): array {
    return ['profile', 'signature', 'documents', 'certificates', 'payments', 'attendance', 'gallery', 'other'];
}
function safe_member_uid(string $uid): string {
    return preg_replace('/[^A-Za-z0-9_-]/', '', $uid) ?: 'member_' . secure_token();
}
function member_storage_root(string $memberUid): string {
    return __DIR__ . '/../uploads/members/' . safe_member_uid($memberUid);
}
function member_storage_public_path(string $memberUid, string $category, string $filename = ''): string {
    $category = in_array($category, member_storage_categories(), true) ? $category : 'other';
    return 'uploads/members/' . safe_member_uid($memberUid) . '/' . $category . ($filename ? '/' . $filename : '');
}
function create_member_storage(string $memberUid): void {
    $root = member_storage_root($memberUid);
    foreach (member_storage_categories() as $category) {
        $dir = $root . '/' . $category;
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $index = $dir . '/index.html';
        if (!file_exists($index)) file_put_contents($index, '');
    }
}
function normalize_member_file_category(string $category): string {
    return in_array($category, member_storage_categories(), true) ? $category : 'other';
}
function format_bytes(int $bytes): string {
    $units = ['B', 'KB', 'MB', 'GB']; $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) { $bytes /= 1024; $i++; }
    return round($bytes, $i ? 1 : 0) . ' ' . $units[$i];
}
function member_file_icon(string $extension): string {
    $extension = strtolower($extension);
    if (in_array($extension, ['jpg','jpeg','png','webp','gif'], true)) return 'bi-file-earmark-image';
    if ($extension === 'pdf') return 'bi-file-earmark-pdf';
    if (in_array($extension, ['doc','docx'], true)) return 'bi-file-earmark-word';
    if (in_array($extension, ['xls','xlsx'], true)) return 'bi-file-earmark-excel';
    if (in_array($extension, ['ppt','pptx'], true)) return 'bi-file-earmark-ppt';
    if ($extension === 'zip') return 'bi-file-earmark-zip';
    if (in_array($extension, ['mp3','wav'], true)) return 'bi-file-earmark-music';
    if (in_array($extension, ['mp4','mov','avi','webm'], true)) return 'bi-file-earmark-play';
    return 'bi-file-earmark';
}
function upload_member_file(array $file, array $member, string $category, string $notes = ''): ?array {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    $config = require __DIR__ . '/../config/config.php';
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > $config['security']['upload_max_bytes']) throw new RuntimeException('Invalid upload size.');
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $config['security']['allowed_uploads'], true)) throw new RuntimeException('File type not allowed.');
    $memberUid = safe_member_uid($member['member_uid']);
    $category = normalize_member_file_category($category);
    create_member_storage($memberUid);
    $storedName = date('Ymd_His') . '_' . secure_token() . '.' . $extension;
    $dir = member_storage_root($memberUid) . '/' . $category;
    $target = $dir . '/' . $storedName;
    if (!move_uploaded_file($file['tmp_name'], $target)) throw new RuntimeException('Upload failed.');
    $relativePath = member_storage_public_path($memberUid, $category, $storedName);
    $mime = mime_content_type($target) ?: 'application/octet-stream';
    $stmt = db()->prepare('INSERT INTO member_files(member_id, category, file_name, original_file_name, file_path, mime_type, file_extension, file_size, uploaded_by, status, notes) VALUES(?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([(int)$member['id'], $category, $storedName, $file['name'], $relativePath, $mime, $extension, (int)$file['size'], $_SESSION['user']['id'] ?? null, 'active', $notes]);
    return ['id' => db()->lastInsertId(), 'path' => $relativePath, 'name' => $storedName];
}
function upload_member_files(array $files, array $member, string $category, string $notes = ''): int {
    $count = 0;
    foreach (($files['name'] ?? []) as $i => $name) {
        $file = ['name'=>$name, 'type'=>$files['type'][$i] ?? '', 'tmp_name'=>$files['tmp_name'][$i] ?? '', 'error'=>$files['error'][$i] ?? UPLOAD_ERR_NO_FILE, 'size'=>$files['size'][$i] ?? 0];
        if (upload_member_file($file, $member, $category, $notes)) $count++;
    }
    return $count;
}
