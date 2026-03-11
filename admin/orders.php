<?php
require_once __DIR__ . '/../config/db.php';
require_admin();

$class = trim($_GET['class'] ?? '');
$gender = trim($_GET['gender'] ?? '');
$item = trim($_GET['item_name'] ?? '');
$date = trim($_GET['order_date'] ?? '');

$sql = 'SELECT student_name, class, gender, class_number, item_type, item_name, pages, quantity, total_price, order_date FROM orders WHERE 1=1';
$params = [];

if ($class !== '') {
    $sql .= ' AND class = :class';
    $params[':class'] = $class;
}
if ($gender !== '') {
    $sql .= ' AND gender = :gender';
    $params[':gender'] = $gender;
}
if ($item !== '') {
    $sql .= ' AND item_name LIKE :item';
    $params[':item'] = '%' . $item . '%';
}
if ($date !== '') {
    $sql .= ' AND DATE(order_date) = :order_date';
    $params[':order_date'] = $date;
}
$sql .= ' ORDER BY order_date DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$q = http_build_query(array_filter([
    'class' => $class,
    'gender' => $gender,
    'item_name' => $item,
    'order_date' => $date,
]));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-madrasa navbar-dark"><div class="container"><a class="navbar-brand" href="dashboard.php">Admin Panel</a><div class="ms-auto d-flex gap-2"><a class="btn btn-outline-light btn-sm" href="textbooks.php">Textbooks</a><a class="btn btn-outline-light btn-sm" href="notebooks.php">Notebooks</a><a class="btn btn-light btn-sm" href="logout.php">Logout</a></div></div></nav>
<div class="container py-4">
    <div class="form-section mb-3">
        <form class="row g-2" method="get">
            <div class="col-md-2"><select class="form-select" name="class"><option value="">All Class</option><?php for ($i=1; $i<=12; $i++): ?><option value="<?= $i ?>" <?= $class === (string)$i ? 'selected' : '' ?>><?= $i ?></option><?php endfor; ?></select></div>
            <div class="col-md-2"><select class="form-select" name="gender"><option value="">All Gender</option><option value="Male" <?= $gender==='Male'?'selected':'' ?>>Male</option><option value="Female" <?= $gender==='Female'?'selected':'' ?>>Female</option></select></div>
            <div class="col-md-3"><input class="form-control" name="item_name" placeholder="Book Name" value="<?= e($item) ?>"></div>
            <div class="col-md-2"><input class="form-control" type="date" name="order_date" value="<?= e($date) ?>"></div>
            <div class="col-md-3 d-flex gap-2"><button class="btn btn-madrasa">Filter</button><a class="btn btn-outline-secondary" href="orders.php">Reset</a><a class="btn btn-outline-success" href="export_orders.php?<?= e($q) ?>">Export CSV</a></div>
        </form>
    </div>

    <div class="form-section">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead><tr><th>Student</th><th>Class</th><th>Gender</th><th>Class#</th><th>Item</th><th>Type</th><th>Qty</th><th>Total</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr>
                        <td><?= e($o['student_name']) ?></td>
                        <td><?= e($o['class']) ?></td>
                        <td><?= e($o['gender']) ?></td>
                        <td><?= e((string)$o['class_number']) ?></td>
                        <td><?= e($o['item_name'] . ($o['pages'] ? ' (' . $o['pages'] . 'p)' : '')) ?></td>
                        <td><?= e($o['item_type']) ?></td>
                        <td><?= (int)$o['quantity'] ?></td>
                        <td>₹<?= number_format((float)$o['total_price'], 2) ?></td>
                        <td><?= e($o['order_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$orders): ?><tr><td colspan="9" class="text-center">No orders found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
