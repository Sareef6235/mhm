<form method="post" class="card card-body">
<?= csrf_field() ?><input type="hidden" name="id" value="<?= e((string)($student['id'] ?? '')) ?>">
<div class="row g-3">
<?php
$labels = ['student_id'=>'Student ID','admission_no'=>'Admission Number','name'=>'Name *','register_num'=>'Register Number','class'=>'Class','section'=>'Section','chest_no'=>'Chest Number','house_name'=>'House Name','phone'=>'Phone','dob'=>'Date of Birth','status'=>'Status','role'=>'Role','item_type'=>'Item Name','item_icon'=>'Item Icon','attendance_status'=>'Attendance Status','notice_id'=>'Notice ID'];
foreach ($labels as $key => $label): ?>
    <div class="col-md-4"><label class="form-label"><?= e($label) ?></label><input class="form-control" name="<?= e($key) ?>" value="<?= e(post_value($key, $student ?? [])) ?>" <?= $key === 'name' ? 'required' : '' ?>></div>
<?php endforeach; ?>
</div><div class="mt-4"><button class="btn btn-dark">Save Student</button><a href="students.php" class="btn btn-outline-secondary">Cancel</a></div></form>
