<?php
require_once __DIR__ . '/../config/db.php';

$studentName = trim($_GET['student_name'] ?? '');
$class = trim($_GET['class'] ?? '');
$gender = trim($_GET['gender'] ?? '');
$classNumber = trim($_GET['class_number'] ?? '');
$orders = [];

if ($studentName !== '' && $class !== '' && $gender !== '' && $classNumber !== '') {
    $stmt = $pdo->prepare('SELECT item_type, item_name, pages, quantity, price, total_price, order_date FROM orders WHERE student_name = :student_name AND class = :class AND gender = :gender AND class_number = :class_number ORDER BY order_date DESC, id DESC');
    $stmt->execute([
        ':student_name' => $studentName,
        ':class' => $class,
        ':gender' => $gender,
        ':class_number' => $classNumber,
    ]);
    $orders = $stmt->fetchAll();
}
?>
<!doctype html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Orders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css"></head>
<body>
<nav class="navbar navbar-expand-lg bg-madrasa navbar-dark"><div class="container">
<a class="navbar-brand" href="index.php">Madrasa Book Order</a>
<div class="ms-auto d-flex gap-2"><a class="btn btn-outline-light btn-sm" href="order.php">Order</a><a class="btn btn-light btn-sm" href="../admin/login.php">Admin</a></div>
</div></nav>
<div class="container py-4"><div class="form-section mb-3"><h5>Track My Orders</h5>
<form method="get" class="row g-2">
<div class="col-md-3"><input name="student_name" class="form-control" placeholder="Student Name" value="<?=e($studentName)?>" required></div>
<div class="col-md-2"><select name="class" class="form-select" required><option value="">Class</option><?php for($i=1;$i<=12;$i++):?><option value="<?=$i?>" <?=$class===(string)$i?'selected':''?>><?=$i?></option><?php endfor;?></select></div>
<div class="col-md-2"><select name="gender" class="form-select" required><option value="">Gender</option><option value="Male" <?=$gender==='Male'?'selected':''?>>Male</option><option value="Female" <?=$gender==='Female'?'selected':''?>>Female</option></select></div>
<div class="col-md-2"><input name="class_number" class="form-control" placeholder="Class No" value="<?=e($classNumber)?>" required></div>
<div class="col-md-3"><button class="btn btn-madrasa">Search</button></div>
</form></div>
<div class="form-section"><div class="table-responsive"><table class="table table-striped align-middle">
<thead><tr><th>Item</th><th>Type</th><th>Qty</th><th>Price</th><th>Total</th><th>Date</th></tr></thead><tbody>
<?php foreach ($orders as $order): ?><tr><td><?= e($order['item_name'] . ($order['pages'] ? ' (' . $order['pages'] . ' pages)' : '')) ?></td><td><?= e($order['item_type']) ?></td><td><?= (int)$order['quantity'] ?></td><td>₹<?= number_format((float)$order['price'], 2) ?></td><td>₹<?= number_format((float)$order['total_price'], 2) ?></td><td><?= e($order['order_date']) ?></td></tr><?php endforeach; ?>
<?php if (!$orders): ?><tr><td colspan="6" class="text-center">No orders found.</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?php render_site_footer(); ?>
</body></html>
