<?php
require_once __DIR__ . '/../config/db.php';
require_admin();

$classFilter = trim($_GET['class'] ?? '');
$subjectFilter = trim($_GET['subject'] ?? '');
$nameSearch = trim($_GET['student_name'] ?? '');

$sql = 'SELECT student_name, class, phone, book_name, price, quantity, total_price, order_date FROM orders WHERE 1=1';
$params = [];

if ($classFilter !== '') {
    $sql .= ' AND class = :class';
    $params[':class'] = $classFilter;
}
if ($subjectFilter !== '') {
    $sql .= ' AND book_name = :book_name';
    $params[':book_name'] = $subjectFilter;
}
if ($nameSearch !== '') {
    $sql .= ' AND student_name LIKE :student_name';
    $params[':student_name'] = '%' . $nameSearch . '%';
}

$sql .= ' ORDER BY order_date DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=orders_export_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Student Name', 'Class', 'Phone', 'Book', 'Price', 'Quantity', 'Total Price', 'Order Date']);

foreach ($orders as $order) {
    fputcsv($output, [
        $order['student_name'],
        $order['class'],
        $order['phone'],
        $order['book_name'],
        $order['price'],
        $order['quantity'],
        $order['total_price'],
        $order['order_date'],
    ]);
}

fclose($output);
exit;
