<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

$where = [];
$params = [];
$types = '';
$filters = ['name' => 'LIKE', 'class' => '=', 'section' => '=', 'status' => '=', 'register_num' => '=', 'admission_no' => '='];
foreach ($filters as $field => $op) {
    $value = trim((string)($_GET[$field] ?? ''));
    if ($value !== '') {
        $where[] = $op === 'LIKE' ? "$field LIKE ?" : "$field = ?";
        $params[] = $op === 'LIKE' ? "%$value%" : $value;
        $types .= 's';
    }
}
$sql = 'SELECT * FROM users' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY created_at DESC, id DESC LIMIT 300';
$stmt = $conn->prepare($sql);
if ($params) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$students = $stmt->get_result();
$stmt->close();
$classes = $conn->query("SELECT DISTINCT class FROM users WHERE class IS NOT NULL AND class <> '' ORDER BY class ASC");
render_header('Students');
?>
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3">Students</h1><a href="student-add.php" class="btn btn-dark">Add Student</a></div>
<form class="card card-body mb-3" method="get"><div class="row g-2">
    <div class="col-md-3"><input class="form-control" name="name" placeholder="Search name" value="<?= e($_GET['name'] ?? '') ?>"></div>
    <div class="col-md-2"><select class="form-select" name="class"><option value="">Class</option><?php while ($classes && $c = $classes->fetch_assoc()): ?><option <?= (($_GET['class'] ?? '')===$c['class'])?'selected':'' ?>><?= e($c['class']) ?></option><?php endwhile; ?></select></div>
    <div class="col-md-2"><input class="form-control" name="section" placeholder="Section" value="<?= e($_GET['section'] ?? '') ?>"></div>
    <div class="col-md-2"><input class="form-control" name="status" placeholder="Status" value="<?= e($_GET['status'] ?? '') ?>"></div>
    <div class="col-md-3"><div class="input-group"><input class="form-control" name="register_num" placeholder="Register No" value="<?= e($_GET['register_num'] ?? '') ?>"><input class="form-control" name="admission_no" placeholder="Admission No" value="<?= e($_GET['admission_no'] ?? '') ?>"><button class="btn btn-outline-dark">Filter</button></div></div>
</div></form>
<form method="post" action="generate-cards.php" class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-striped align-middle mb-0"><thead><tr><th><input type="checkbox" data-check-all=".student-check"></th><th>Name</th><th>Class</th><th>Section</th><th>Register</th><th>Admission</th><th>Chest</th><th>Phone</th><th class="table-actions">Actions</th></tr></thead><tbody>
<?php while ($row = $students->fetch_assoc()): ?><tr><td><input class="student-check" type="checkbox" name="student_ids[]" value="<?= (int)$row['id'] ?>"></td><td><?= e($row['name']) ?></td><td><?= e($row['class']) ?></td><td><?= e($row['section']) ?></td><td><?= e($row['register_num']) ?></td><td><?= e($row['admission_no']) ?></td><td><?= e($row['chest_no']) ?></td><td><?= e($row['phone']) ?></td><td><a class="btn btn-sm btn-outline-dark" href="student-edit.php?id=<?= (int)$row['id'] ?>">Edit</a> <a data-confirm="Delete this student?" class="btn btn-sm btn-outline-danger" href="student-delete.php?id=<?= (int)$row['id'] ?>&csrf_token=<?= e(csrf_token()) ?>">Delete</a></td></tr><?php endwhile; ?>
</tbody></table></div></div><div class="card-footer d-flex gap-2"><input type="hidden" name="selection_mode" value="selected"><button class="btn btn-dark">Generate Selected</button><a class="btn btn-outline-dark" href="generate-cards.php">Bulk Options</a></div></form>
<?php render_footer(); ?>
