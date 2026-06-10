<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';
$fields = ['student_id','admission_no','name','item_icon','item_type','attendance_status','notice_id','register_num','class','dob','role','phone','status','chest_no','section','house_name'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (trim($_POST['name'] ?? '') === '') { flash('Name is required.', 'danger'); }
    else {
        $values = [];
        foreach ($fields as $f) { $values[$f] = trim((string)($_POST[$f] ?? '')); }
        $columns = implode(',', array_keys($values));
        $marks = implode(',', array_fill(0, count($values), '?'));
        $stmt = $conn->prepare("INSERT INTO users ($columns, created_at) VALUES ($marks, NOW())");
        $types = str_repeat('s', count($values));
        $stmt->bind_param($types, ...array_values($values));
        $stmt->execute();
        $stmt->close();
        log_card_action($conn, 'student_created', $values['name']);
        flash('Student added successfully.');
        redirect('students.php');
    }
}
render_header('Add Student');
?>
<h1 class="h3 mb-3">Add Student</h1>
<?php include __DIR__ . '/student-form.php'; ?>
<?php render_footer(); ?>
