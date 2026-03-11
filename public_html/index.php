<?php
require_once __DIR__ . '/../config/db.php';

$classCounts = $pdo->query('SELECT class, COUNT(*) AS total_books FROM books GROUP BY class ORDER BY CAST(class AS UNSIGNED)')->fetchAll();
$map = [];
foreach ($classCounts as $row) {
    $map[(int)$row['class']] = (int)$row['total_books'];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Madrasa Book Ordering</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-madrasa navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Madrasa Book Order</a>
        <div class="ms-auto d-flex gap-2">
            <a class="btn btn-light btn-sm" href="order.php">Book Now</a>
            <a class="btn btn-outline-light btn-sm" href="myorders.php">My Orders</a>
            <a class="btn btn-outline-light btn-sm" href="../admin/login.php">Admin</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="p-4 p-md-5 mb-4 rounded-4 text-white bg-madrasa">
        <h1 class="display-6">Madrasa Book Ordering</h1>
        <p class="lead mb-0">Select Class 1 to 12, expand the dropdown, and book books instantly.</p>
    </div>

    <div class="form-section">
        <h4 class="mb-3">Classes (1-12)</h4>
        <div class="row g-3">
            <?php for ($i = 1; $i <= 12; $i++): ?>
                <div class="col-6 col-md-3">
                    <div class="card card-soft h-100">
                        <div class="card-body">
                            <h6 class="mb-1">Class <?= $i ?></h6>
                            <small class="text-muted"><?= (int)($map[$i] ?? 0) ?> books available</small>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
        <a href="order.php" class="btn btn-madrasa mt-3">Open Class Dropdown Booking</a>
    </div>
</div>
</body>
</html>
