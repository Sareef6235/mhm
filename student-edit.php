<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $conn->prepare('SELECT * FROM users WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$student) { flash('Student not found.', 'danger'); redirect('students.php'); }
$fields = ['student_id','admission_no','name','item_icon','item_type','attendance_status','notice_id','register_num','class','dob','role','phone','status','chest_no','section','house_name'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (trim($_POST['name'] ?? '') === '') { flash('Name is required.', 'danger'); }
    else {
        $values = [];
        foreach ($fields as $f) { $values[$f] = trim((string)($_POST[$f] ?? '')); }
        $set = implode(', ', array_map(fn($f) => "$f = ?", array_keys($values)));
        $stmt = $conn->prepare("UPDATE users SET $set WHERE id = ?");
        $types = str_repeat('s', count($values)) . 'i';
        $bind = array_values($values); $bind[] = $id;
        $stmt->bind_param($types, ...$bind);
        $stmt->execute();
        $stmt->close();
        log_card_action($conn, 'student_updated', $values['name']);
        flash('Student updated successfully.');
        redirect('students.php');
    }
}
render_header('Edit Student');
?>
<h1 class="h3 mb-3">Edit Student</h1>
<?php include __DIR__ . '/student-form.php'; ?>
<?php render_footer(); ?>
