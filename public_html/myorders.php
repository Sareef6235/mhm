<?php
require_once __DIR__ . '/../config/db.php';

$phone = trim($_GET['phone'] ?? '');
$orders = [];

if ($phone !== '') {
    $stmt = $pdo->prepare('SELECT student_name, class, phone, book_name, price, quantity, total_price, order_date FROM orders WHERE phone = :phone ORDER BY order_date DESC');
    $stmt->execute([':phone' => $phone]);
    $orders = $stmt->fetchAll();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-madrasa navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Madrasa Book Order</a>
        <div class="ms-auto d-flex gap-2">
            <a class="btn btn-outline-light btn-sm" href="index.php">Home</a>
            <a class="btn btn-outline-light btn-sm" href="order.php">Book</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="form-section">
        <h4 class="mb-3">My Orders</h4>
        <form class="row g-2 mb-3" method="get">
            <div class="col-md-6">
                <input type="text" name="phone" class="form-control" placeholder="Enter your phone number" value="<?= e($phone) ?>" required>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-madrasa" type="submit">Search</button>
            </div>
        </form>

        <?php if ($phone !== '' && !$orders): ?>
            <div class="alert alert-warning">No orders found for this phone number.</div>
        <?php endif; ?>

        <?php if ($orders): ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Book</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= e($order['student_name']) ?></td>
                            <td><?= e($order['class']) ?></td>
                            <td><?= e($order['book_name']) ?></td>
                            <td><?= (int)$order['quantity'] ?></td>
                            <td>৳<?= number_format((float)$order['total_price'], 2) ?></td>
                            <td><?= e($order['order_date']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
