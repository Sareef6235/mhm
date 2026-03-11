<?php
require_once __DIR__ . '/../config/db.php';
require_admin();

$totalStudents = (int)$pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$totalOrders = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalBooks = (int)$pdo->query("SELECT COALESCE(SUM(quantity),0) FROM orders WHERE item_type='textbook'")->fetchColumn();
$totalNotebooks = (int)$pdo->query("SELECT COALESCE(SUM(quantity),0) FROM orders WHERE item_type='notebook'")->fetchColumn();
$totalAmount = (float)$pdo->query('SELECT COALESCE(SUM(total_price),0) FROM orders')->fetchColumn();

$bookAnalytics = $pdo->query("SELECT item_name, SUM(quantity) qty FROM orders WHERE item_type='textbook' GROUP BY item_name ORDER BY qty DESC LIMIT 5")->fetchAll();
$classAnalytics = $pdo->query('SELECT class, COUNT(*) total FROM orders GROUP BY class ORDER BY CAST(class AS UNSIGNED)')->fetchAll();
$genderAnalytics = $pdo->query('SELECT gender, COUNT(*) total FROM orders GROUP BY gender')->fetchAll();
?>
<!doctype html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css"></head>
<body>
<nav class="navbar navbar-expand-lg bg-madrasa navbar-dark"><div class="container">
<a class="navbar-brand" href="dashboard.php">Admin Panel</a>
<div class="ms-auto d-flex gap-2">
<a class="btn btn-outline-light btn-sm" href="textbooks.php">Textbooks</a>
<a class="btn btn-outline-light btn-sm" href="notebooks.php">Notebooks</a>
<a class="btn btn-outline-light btn-sm" href="orders.php">Orders</a>
<a class="btn btn-light btn-sm" href="logout.php">Logout</a></div></div></nav>

<div class="container py-4">
<div class="row g-3 mb-4">
<div class="col-md-4 col-lg"><div class="card card-soft summary-card"><div class="card-body"><h6>Total Students</h6><h4><?= $totalStudents ?></h4></div></div></div>
<div class="col-md-4 col-lg"><div class="card card-soft summary-card"><div class="card-body"><h6>Total Orders</h6><h4><?= $totalOrders ?></h4></div></div></div>
<div class="col-md-4 col-lg"><div class="card card-soft summary-card"><div class="card-body"><h6>Total Books Ordered</h6><h4><?= $totalBooks ?></h4></div></div></div>
<div class="col-md-4 col-lg"><div class="card card-soft summary-card"><div class="card-body"><h6>Total Notebooks Ordered</h6><h4><?= $totalNotebooks ?></h4></div></div></div>
<div class="col-md-4 col-lg"><div class="card card-soft summary-card"><div class="card-body"><h6>Total Amount</h6><h4>₹<?= number_format($totalAmount,2) ?></h4></div></div></div>
</div>

<div class="row g-3">
<div class="col-lg-6"><div class="form-section"><h5>Most Ordered Books</h5><ul class="list-group list-group-flush"><?php foreach($bookAnalytics as $b): ?><li class="list-group-item d-flex justify-content-between"><span><?= e($b['item_name']) ?></span><span><?= (int)$b['qty'] ?></span></li><?php endforeach; ?></ul></div></div>
<div class="col-lg-6"><div class="form-section"><h5>Gender Orders</h5><canvas id="genderChart"></canvas></div></div>
<div class="col-lg-6"><div class="form-section"><h5>Orders Per Class</h5><canvas id="classChart"></canvas></div></div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const classLabels = <?= json_encode(array_column($classAnalytics,'class')) ?>;
const classData = <?= json_encode(array_map('intval', array_column($classAnalytics,'total'))) ?>;
const genderLabels = <?= json_encode(array_column($genderAnalytics,'gender')) ?>;
const genderData = <?= json_encode(array_map('intval', array_column($genderAnalytics,'total'))) ?>;
new Chart(document.getElementById('classChart'), {type:'bar', data:{labels:classLabels, datasets:[{label:'Orders', data:classData, backgroundColor:'#0f6d49'}]}});
new Chart(document.getElementById('genderChart'), {type:'doughnut', data:{labels:genderLabels, datasets:[{data:genderData, backgroundColor:['#0f6d49','#7acb93']}]}});
</script>
<?php render_site_footer(); ?>
</body></html>
