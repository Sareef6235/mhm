<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
$template = get_template($conn, (int)($_REQUEST['template_id'] ?? 0));
$ids = selected_student_ids($conn);
$students = fetch_students_by_ids($conn, $ids);
$file = 'student-cards-' . date('Ymd-His') . '.html';
record_export($conn, 'pdf', $template ? (int)$template['id'] : null, count($students), $file);
log_card_action($conn, 'pdf_export', count($students) . ' cards');
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Print Ready Cards</title><link href="assets/css/style.css" rel="stylesheet"><style>body{background:#fff}.pdf-toolbar{position:sticky;top:0;background:#fff;border-bottom:1px solid #000;padding:10px;z-index:9}@media print{.pdf-toolbar{display:none!important}}</style></head><body><div class="pdf-toolbar"><strong>PDF Export:</strong> use Print / Save as PDF for a cPanel-compatible PDF download. <button onclick="window.print()">Print / Save PDF</button> <a href="generate-cards.php">Back</a></div><main><div class="card-page-grid"><?php foreach($students as $student){ echo render_card_html($template, $student, true); } ?></div></main><script>window.addEventListener('load',()=>setTimeout(()=>window.print(),400));</script></body></html>
