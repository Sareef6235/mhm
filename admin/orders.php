<?php
require_once __DIR__ . '/../config/db.php';
require_admin();

$classFilter = trim($_GET['class'] ?? '');
$subjectFilter = trim($_GET['subject'] ?? '');
$nameSearch = trim($_GET['student_name'] ?? '');

$sql = 'SELECT student_name, class, phone, book_name, quantity, total_price, order_date FROM orders WHERE 1=1';
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

$classes = $pdo->query('SELECT DISTINCT class FROM orders ORDER BY class')->fetchAll();
$subjects = $pdo->query('SELECT DISTINCT book_name FROM orders ORDER BY book_name')->fetchAll();

$queryString = http_build_query(array_filter([
    'class' => $classFilter,
    'subject' => $subjectFilter,
    'student_name' => $nameSearch,
]));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-madrasa navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
        <div class="ms-auto d-flex gap-2">
            <a href="books.php" class="btn btn-outline-light btn-sm">Books</a>
            <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="form-section mb-3">
        <form class="row g-2" method="get">
            <div class="col-md-3">
                <select name="class" class="form-select">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $item): ?>
                        <option value="<?= e($item['class']) ?>" <?= $classFilter === $item['class'] ? 'selected' : '' ?>><?= e($item['class']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="subject" class="form-select">
                    <option value="">All Subjects</option>
                    <?php foreach ($subjects as $item): ?>
                        <option value="<?= e($item['book_name']) ?>" <?= $subjectFilter === $item['book_name'] ? 'selected' : '' ?>><?= e($item['book_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" name="student_name" value="<?= e($nameSearch) ?>" placeholder="Search student name">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-madrasa" type="submit">Filter</button>
                <a class="btn btn-outline-secondary" href="orders.php">Reset</a>
                <a class="btn btn-outline-success" href="export_orders.php?<?= e($queryString) ?>">Export CSV</a>
            </div>
        </form>
    </div>

    <div class="form-section">
        <h5>Orders List</h5>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead><tr><th>Student Name</th><th>Class</th><th>Book</th><th>Quantity</th><th>Total Price</th><th>Date</th></tr></thead>
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
                <?php if (!$orders): ?><tr><td colspan="6" class="text-center">No orders found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
