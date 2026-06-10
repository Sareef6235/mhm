<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/classes/SimpleXlsxWriter.php';
$ids = selected_student_ids($conn);
$students = fetch_students_by_ids($conn, $ids);
$headers = ['Name','Class','Register Number','Admission Number','Chest Number','Section','House','Phone'];
$rows = [];
foreach ($students as $s) { $rows[] = [$s['name'] ?? '', $s['class'] ?? '', $s['register_num'] ?? '', $s['admission_no'] ?? '', $s['chest_no'] ?? '', $s['section'] ?? '', $s['house_name'] ?? '', $s['phone'] ?? '']; }
$file = 'student-cards-' . date('Ymd-His') . '.xlsx';
record_export($conn, 'excel', null, count($rows), $file); log_card_action($conn, 'excel_export', count($rows) . ' students');
SimpleXlsxWriter::output($file, $headers, $rows);
