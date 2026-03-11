<?php
require_once __DIR__ . '/../config/db.php';
require_admin();

$totalOrders = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalBooks = (int)$pdo->query('SELECT COALESCE(SUM(quantity),0) FROM orders')->fetchColumn();
$totalAmount = (float)$pdo->query('SELECT COALESCE(SUM(total_price),0) FROM orders')->fetchColumn();
$totalStudents = (int)$pdo->query('SELECT COUNT(DISTINCT phone) FROM orders')->fetchColumn();

$summaryStmt = $pdo->query('SELECT book_name, SUM(quantity) AS total_qty FROM orders GROUP BY book_name ORDER BY total_qty DESC');
$bookSummary = $summaryStmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-madrasa navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
        <div class="ms-auto d-flex gap-2">
            <a href="books.php" class="btn btn-outline-light btn-sm">Books</a>
            <a href="orders.php" class="btn btn-outline-light btn-sm">Orders</a>
            <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <h3 class="mb-3">Welcome, <?= e((string)$_SESSION['admin_username']) ?></h3>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card card-soft summary-card"><div class="card-body"><h6>Total Orders</h6><h4><?= $totalOrders ?></h4></div></div></div>
        <div class="col-md-3"><div class="card card-soft summary-card"><div class="card-body"><h6>Total Books Ordered</h6><h4><?= $totalBooks ?></h4></div></div></div>
        <div class="col-md-3"><div class="card card-soft summary-card"><div class="card-body"><h6>Total Amount</h6><h4>৳<?= number_format($totalAmount, 2) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card card-soft summary-card"><div class="card-body"><h6>Number of Students</h6><h4><?= $totalStudents ?></h4></div></div></div>
    </div>

    <div class="form-section">
        <h5>Book Order Summary</h5>
        <ul class="list-group list-group-flush">
            <?php foreach ($bookSummary as $item): ?>
                <li class="list-group-item d-flex justify-content-between">
                    <span><?= e($item['book_name']) ?> Book</span>
                    <span><?= (int)$item['total_qty'] ?> orders</span>
                </li>
            <?php endforeach; ?>
            <?php if (!$bookSummary): ?><li class="list-group-item">No orders yet.</li><?php endif; ?>
        </ul>
        <div class="mt-3 fw-semibold">Total Amount Collected: ৳<?= number_format($totalAmount, 2) ?></div>
    </div>
</div>
</body>
</html>
