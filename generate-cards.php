<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';
$templateId = (int)($_REQUEST['template_id'] ?? 0);
$template = get_template($conn, $templateId);
$templates = get_templates($conn);
$ids = selected_student_ids($conn);
$students = fetch_students_by_ids($conn, $ids);
$classes = $conn->query("SELECT DISTINCT class FROM users WHERE class IS NOT NULL AND class <> '' ORDER BY class ASC");
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $students) { log_card_action($conn, 'cards_previewed', count($students) . ' cards'); }
render_header('Generate Cards');
?>
<div class="d-flex justify-content-between align-items-center mb-3 no-print"><h1 class="h3">Generate Cards</h1><div><button onclick="window.print()" class="btn btn-outline-dark">Print Preview</button></div></div>
<form method="post" class="card card-body mb-4 no-print"><div class="row g-3"><div class="col-md-4"><label class="form-label">Template</label><select class="form-select" name="template_id"><?php foreach($templates as $tpl): ?><option value="<?= (int)$tpl['id'] ?>" <?= ($template && (int)$template['id']===(int)$tpl['id'])?'selected':'' ?>><?= e($tpl['template_name']) ?></option><?php endforeach; ?></select></div><div class="col-md-4"><label class="form-label">Selection Mode</label><select class="form-select" name="selection_mode"><option value="selected">Selected student IDs</option><option value="class">Entire class</option><option value="all">All students</option></select></div><div class="col-md-4"><label class="form-label">Class for Bulk Class Mode</label><select class="form-select" name="bulk_class"><option value="">Choose class</option><?php while($classes && $c=$classes->fetch_assoc()): ?><option><?= e($c['class']) ?></option><?php endwhile; ?></select></div><div class="col-12"><label class="form-label">Student IDs for Selected Mode</label><input class="form-control" name="student_ids" placeholder="Example: 1,2,3" oninput="this.name='student_ids[]'"><div class="form-help">Tip: use the Students page to tick multiple students, or choose Class/All here.</div></div></div><div class="mt-3 d-flex gap-2"><button class="btn btn-dark">Preview Cards</button><button formaction="export-pdf.php" class="btn btn-outline-dark">PDF / Print Export</button><button formaction="export-excel.php" class="btn btn-outline-dark">Excel Export</button></div></form>
<?php if (!$students): ?><div class="alert alert-info no-print">Select one student, multiple students, an entire class, or all students to preview cards.</div><?php else: ?>
<div class="no-print mb-3"><form method="post" action="export-pdf.php" class="d-inline"><?php foreach($ids as $id): ?><input type="hidden" name="student_ids[]" value="<?= (int)$id ?>"><?php endforeach; ?><input type="hidden" name="selection_mode" value="selected"><input type="hidden" name="template_id" value="<?= (int)$template['id'] ?>"><button class="btn btn-dark">Download / Print PDF</button></form> <form method="post" action="export-excel.php" class="d-inline"><?php foreach($ids as $id): ?><input type="hidden" name="student_ids[]" value="<?= (int)$id ?>"><?php endforeach; ?><input type="hidden" name="selection_mode" value="selected"><button class="btn btn-outline-dark">Download Excel</button></form></div>
<div class="card-page-grid"><?php foreach($students as $student) { echo render_card_html($template, $student, true); } ?></div>
<?php endif; render_footer(); ?>
