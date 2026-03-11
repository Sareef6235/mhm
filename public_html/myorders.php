<?php
require_once __DIR__ . '/../config/db.php';
require_student();
$student = $_SESSION['student'];

$stmt = $pdo->prepare('SELECT item_type, item_name, pages, quantity, price, total_price, order_date FROM orders WHERE class = :class AND gender = :gender AND class_number = :class_number ORDER BY order_date DESC, id DESC');
$stmt->execute([
    ':class' => $student['class'],
    ':gender' => $student['gender'],
    ':class_number' => $student['class_number'],
]);
$orders = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Orders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css"></head>
<body>
<nav class="navbar navbar-expand-lg bg-madrasa navbar-dark"><div class="container">
<a class="navbar-brand" href="order.php">Madrasa Book Order</a>
<div class="ms-auto d-flex gap-2"><a class="btn btn-outline-light btn-sm" href="order.php">Order</a><a class="btn btn-light btn-sm" href="logout.php">Logout</a></div>
</div></nav>
<div class="container py-4"><div class="form-section">
<h4>My Orders</h4>
<div class="table-responsive"><table class="table table-striped align-middle">
<thead><tr><th>Item</th><th>Type</th><th>Qty</th><th>Price</th><th>Total</th><th>Date</th></tr></thead><tbody>
<?php foreach ($orders as $order): ?>
<tr>
<td><?= e($order['item_name'] . ($order['pages'] ? ' (' . $order['pages'] . ' pages)' : '')) ?></td>
<td><?= e($order['item_type']) ?></td><td><?= (int)$order['quantity'] ?></td><td>₹<?= number_format((float)$order['price'], 2) ?></td><td>₹<?= number_format((float)$order['total_price'], 2) ?></td><td><?= e($order['order_date']) ?></td>
</tr>
<?php endforeach; ?>
<?php if (!$orders): ?><tr><td colspan="6" class="text-center">No orders yet.</td></tr><?php endif; ?>
</tbody></table></div>
</div></div>
</body></html>
