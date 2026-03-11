<?php
require_once __DIR__ . '/../config/db.php';
require_admin();

$classFilter = isset($_GET['class']) ? trim((string)$_GET['class']) : '';
$orderFilter = strtoupper(trim((string)($_GET['order'] ?? 'DESC')));
$orderFilter = in_array($orderFilter, ['ASC', 'DESC'], true) ? $orderFilter : 'DESC';

$validClasses = array_map('strval', range(1, 12));
$selectedClass = in_array($classFilter, $validClasses, true) ? $classFilter : '';

$orderClassColumn = column_exists($pdo, 'orders', 'class') ? 'class' : 'class_id';
$studentClassColumn = column_exists($pdo, 'students', 'class') ? 'class' : 'class_id';

$whereOrders = '';
$whereStudents = '';
$paramsOrders = [];
$paramsStudents = [];
if ($selectedClass !== '') {
    $whereOrders = " WHERE {$orderClassColumn} = :class";
    $whereStudents = " WHERE {$studentClassColumn} = :class";
    $paramsOrders[':class'] = $orderClassColumn === 'class_id' ? (int)$selectedClass : $selectedClass;
    $paramsStudents[':class'] = $studentClassColumn === 'class_id' ? (int)$selectedClass : $selectedClass;
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM students' . $whereStudents);
$stmt->execute($paramsStudents);
$totalStudents = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM orders' . $whereOrders);
$stmt->execute($paramsOrders);
$totalOrders = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM orders" . ($whereOrders ? $whereOrders . " AND item_type='textbook'" : " WHERE item_type='textbook'"));
$stmt->execute($paramsOrders);
$totalBooks = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM orders" . ($whereOrders ? $whereOrders . " AND item_type='notebook'" : " WHERE item_type='notebook'"));
$stmt->execute($paramsOrders);
$totalNotebooks = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COALESCE(SUM(total_price), 0) FROM orders' . $whereOrders);
$stmt->execute($paramsOrders);
$totalAmount = (float)$stmt->fetchColumn();

$booksSql = "SELECT item_name, SUM(quantity) AS qty FROM orders WHERE item_type='textbook'"
    . ($selectedClass !== '' ? " AND {$orderClassColumn} = :class" : '')
    . " GROUP BY item_name ORDER BY qty {$orderFilter} LIMIT 5";
$stmt = $pdo->prepare($booksSql);
$stmt->execute($selectedClass !== '' ? [':class' => ($orderClassColumn === 'class_id' ? (int)$selectedClass : $selectedClass)] : []);
$bookAnalytics = $stmt->fetchAll(PDO::FETCH_ASSOC);

$classSql = "SELECT {$orderClassColumn} AS class_value, COUNT(*) AS total FROM orders"
    . ($selectedClass !== '' ? " WHERE {$orderClassColumn} = :class" : '')
    . " GROUP BY {$orderClassColumn} ORDER BY CAST({$orderClassColumn} AS UNSIGNED) {$orderFilter}";
$stmt = $pdo->prepare($classSql);
$stmt->execute($selectedClass !== '' ? [':class' => ($orderClassColumn === 'class_id' ? (int)$selectedClass : $selectedClass)] : []);
$classAnalytics = $stmt->fetchAll(PDO::FETCH_ASSOC);

$genderSql = "SELECT gender, COUNT(*) AS total FROM orders"
    . ($selectedClass !== '' ? " WHERE {$orderClassColumn} = :class" : '')
    . " GROUP BY gender ORDER BY total {$orderFilter}";
$stmt = $pdo->prepare($genderSql);
$stmt->execute($selectedClass !== '' ? [':class' => ($orderClassColumn === 'class_id' ? (int)$selectedClass : $selectedClass)] : []);
$genderAnalytics = $stmt->fetchAll(PDO::FETCH_ASSOC);

$classLabels = array_map(static fn($row) => (string)$row['class_value'], $classAnalytics);
$classData = array_map('intval', array_column($classAnalytics, 'total'));
$genderLabels = array_column($genderAnalytics, 'gender');
$genderData = array_map('intval', array_column($genderAnalytics, 'total'));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard | Madrasa Book Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container py-1">
        <a class="navbar-brand fw-semibold" href="dashboard.php">Madrasa Book Portal</a>
        <div class="ms-auto d-flex gap-2">
            <a class="btn btn-outline-light btn-sm" href="dashboard.php">Dashboard</a>
            <a class="btn btn-outline-light btn-sm" href="orders.php">Orders</a>
            <a class="btn btn-outline-light btn-sm" href="textbooks.php">Textbooks</a>
            <a class="btn btn-outline-light btn-sm" href="notebooks.php">Notebooks</a>
            <a class="btn btn-light btn-sm" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<main class="container py-4 py-lg-5">
    <div class="row g-3 g-lg-4 mb-2">
        <div class="col-6 col-lg">
            <div class="card summary-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="summary-label mb-1">Total Students</p><p class="summary-value"><?= $totalStudents ?></p></div>
                    <span class="summary-icon">👨‍🎓</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg"><div class="card summary-card"><div class="card-body d-flex justify-content-between align-items-center"><div><p class="summary-label mb-1">Total Orders</p><p class="summary-value"><?= $totalOrders ?></p></div><span class="summary-icon">🧾</span></div></div></div>
        <div class="col-6 col-lg"><div class="card summary-card"><div class="card-body d-flex justify-content-between align-items-center"><div><p class="summary-label mb-1">Total Books</p><p class="summary-value"><?= $totalBooks ?></p></div><span class="summary-icon">📚</span></div></div></div>
        <div class="col-6 col-lg"><div class="card summary-card"><div class="card-body d-flex justify-content-between align-items-center"><div><p class="summary-label mb-1">Total Notebooks</p><p class="summary-value"><?= $totalNotebooks ?></p></div><span class="summary-icon">📓</span></div></div></div>
        <div class="col-12 col-lg"><div class="card summary-card"><div class="card-body d-flex justify-content-between align-items-center"><div><p class="summary-label mb-1">Total Amount</p><p class="summary-value" style="color:var(--accent)">₹<?= number_format($totalAmount, 2) ?></p></div><span class="summary-icon">💰</span></div></div></div>
    </div>

    <div class="panel mb-4">
        <h6 class="mb-3">Filters</h6>
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="class" class="form-label">Class</label>
                <select name="class" id="class" class="form-select">
                    <option value="">All Classes</option>
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= $selectedClass === (string)$i ? 'selected' : '' ?>>Class <?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="order" class="form-label">Sort</label>
                <select name="order" id="order" class="form-select">
                    <option value="DESC" <?= $orderFilter === 'DESC' ? 'selected' : '' ?>>Highest First</option>
                    <option value="ASC" <?= $orderFilter === 'ASC' ? 'selected' : '' ?>>Lowest First</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-madrasa flex-fill">Apply</button>
                <a href="dashboard.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="row g-4">
        <div class="col-xl-5">
            <div class="panel h-100">
                <h6 class="mb-3">Top Ordered Textbooks</h6>
                <?php if ($bookAnalytics): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($bookAnalytics as $index => $book): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rank-badge"><?= $index + 1 ?></span>
                                    <span><?= e($book['item_name']) ?></span>
                                </div>
                                <span class="badge text-bg-success rounded-pill"><?= (int)$book['qty'] ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted mb-0">No textbook orders yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="row g-4">
                <div class="col-12">
                    <div class="panel"><h6 class="mb-3">Gender Analytics</h6><canvas id="genderChart" height="120"></canvas></div>
                </div>
                <div class="col-12">
                    <div class="panel"><h6 class="mb-3">Orders Per Class</h6><canvas id="classChart" height="120"></canvas></div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const classLabels = <?= json_encode($classLabels, JSON_UNESCAPED_UNICODE) ?>;
const classData = <?= json_encode($classData, JSON_UNESCAPED_UNICODE) ?>;
const genderLabels = <?= json_encode($genderLabels, JSON_UNESCAPED_UNICODE) ?>;
const genderData = <?= json_encode($genderData, JSON_UNESCAPED_UNICODE) ?>;

new Chart(document.getElementById('classChart'), {
    type: 'bar',
    data: {labels: classLabels, datasets: [{label: 'Orders', data: classData, backgroundColor: '#0f6d49', borderRadius: 8}]},
    options: {responsive: true, plugins: {legend: {display: false}}, scales: {y: {beginAtZero: true, ticks: {precision: 0}}}}
});

new Chart(document.getElementById('genderChart'), {
    type: 'doughnut',
    data: {labels: genderLabels, datasets: [{data: genderData, backgroundColor: ['#0f6d49', '#7acb93', '#d4af37', '#6c757d'], borderWidth: 0}]},
    options: {responsive: true}
});
</script>
<?php render_site_footer(); ?>
</body>
</html>
