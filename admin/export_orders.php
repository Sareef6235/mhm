<?php
require_once __DIR__ . '/../config/db.php';
require_admin();

$class = trim($_GET['class'] ?? '');
$gender = trim($_GET['gender'] ?? '');
$item = trim($_GET['item_name'] ?? '');
$date = trim($_GET['order_date'] ?? '');

$sql = 'SELECT student_name, class, gender, class_number, phone, item_type, item_name, pages, price, quantity, total_price, order_date FROM orders WHERE 1=1';
$params = [];
if ($class !== '') { $sql .= ' AND class = :class'; $params[':class'] = $class; }
if ($gender !== '') { $sql .= ' AND gender = :gender'; $params[':gender'] = $gender; }
if ($item !== '') { $sql .= ' AND item_name LIKE :item'; $params[':item'] = "%{$item}%"; }
if ($date !== '') { $sql .= ' AND DATE(order_date) = :order_date'; $params[':order_date'] = $date; }
$sql .= ' ORDER BY order_date DESC';

$stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=orders_export_' . date('Ymd_His') . '.csv');
$out = fopen('php://output', 'w');
fputcsv($out, ['Student Name','Class','Gender','Class Number','Phone','Item Type','Item Name','Pages','Price','Quantity','Total Price','Order Date']);
foreach($rows as $r){ fputcsv($out, [$r['student_name'],$r['class'],$r['gender'],$r['class_number'],$r['phone'],$r['item_type'],$r['item_name'],$r['pages'],$r['price'],$r['quantity'],$r['total_price'],$r['order_date']]); }
fclose($out); exit;
