<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) { http_response_code(419); die('Invalid CSRF token.'); }
$id = (int)($_GET['id'] ?? 0);
$count = $conn->query('SELECT COUNT(*) total FROM card_templates')->fetch_assoc()['total'] ?? 0;
if ((int)$count <= 1) { flash('At least one template is required.', 'danger'); redirect('templates.php'); }
$stmt = $conn->prepare('DELETE FROM card_templates WHERE id = ?');
$stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
log_card_action($conn, 'template_deleted', 'ID ' . $id); flash('Template deleted.'); redirect('templates.php');
