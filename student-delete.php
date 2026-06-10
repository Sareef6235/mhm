<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) { http_response_code(419); die('Invalid CSRF token.'); }
$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();
log_card_action($conn, 'student_deleted', 'ID ' . $id);
flash('Student deleted successfully.');
redirect('students.php');
