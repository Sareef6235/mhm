<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

$stats = ['students' => 0, 'templates' => 0, 'generated' => 0, 'downloads' => 0];
foreach ([
    'students' => 'SELECT COUNT(*) total FROM users',
    'templates' => 'SELECT COUNT(*) total FROM card_templates',
    'generated' => "SELECT COALESCE(SUM(student_count),0) total FROM card_exports",
    'downloads' => "SELECT COUNT(*) total FROM card_exports WHERE export_type IN ('pdf','excel')",
] as $key => $sql) {
    $result = $conn->query($sql);
    $stats[$key] = (int)(($result ? $result->fetch_assoc() : [])['total'] ?? 0);
}
$recent = $conn->query('SELECT * FROM card_logs ORDER BY id DESC LIMIT 8');
render_header('Dashboard');
?>
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div><h1 class="h3 mb-1">Dashboard</h1><p class="text-muted mb-0">Production-ready black and white card generation system.</p></div>
    <a href="generate-cards.php" class="btn btn-dark">Generate Cards</a>
</div>
<div class="row g-3 mb-4">
    <?php foreach ([['Total Students',$stats['students']],['Total Templates',$stats['templates']],['Generated Cards',$stats['generated']],['Downloads',$stats['downloads']]] as $card): ?>
    <div class="col-6 col-lg-3"><div class="card stat-card"><div class="card-body"><div class="text-muted small"><?= e($card[0]) ?></div><div class="display-6 fw-bold"><?= e((string)$card[1]) ?></div></div></div></div>
    <?php endforeach; ?>
</div>
<div class="row g-4">
    <div class="col-lg-8"><div class="card"><div class="card-header bg-white fw-bold">Recent Activity</div><div class="card-body p-0"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Action</th><th>Details</th><th>Date</th></tr></thead><tbody>
    <?php while ($recent && $row = $recent->fetch_assoc()): ?><tr><td><?= e($row['action']) ?></td><td><?= e($row['details']) ?></td><td><?= e($row['created_at']) ?></td></tr><?php endwhile; ?>
    </tbody></table></div></div></div></div>
    <div class="col-lg-4"><div class="card"><div class="card-header bg-white fw-bold">Quick Links</div><div class="list-group list-group-flush"><a class="list-group-item list-group-item-action" href="student-add.php">Add Student</a><a class="list-group-item list-group-item-action" href="template-add.php">Create Template</a><a class="list-group-item list-group-item-action" href="export-pdf.php">Bulk PDF / Print</a><a class="list-group-item list-group-item-action" href="export-excel.php">Excel Export</a></div></div></div>
</div>
<?php render_footer(); ?>
