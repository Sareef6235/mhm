<?php
require_once __DIR__ . '/../config/db.php';

$errors = [];
$name = trim($_POST['student_name'] ?? '');
$class = trim($_POST['class'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid request token.';
    }

    if ($name === '' || $class === '') {
        $errors[] = 'Student Name and Class are required.';
    }

    if (!in_array((int)$class, range(1, 12), true)) {
        $errors[] = 'Please select a valid class.';
    }

    if (!$errors) {
        $q = http_build_query([
            'student_name' => $name,
            'class' => $class,
        ]);
        header('Location: order.php?' . $q);
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="login-page">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="form-section">
                <h3 class="mb-3 text-center">Student Entry Form</h3>
                <p class="text-muted text-center">Enter your name and class to continue to ordering.</p>
                <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
                <form method="post" class="row g-3">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="col-md-7">
                        <label class="form-label">Student Name</label>
                        <input name="student_name" class="form-control" value="<?= e($name) ?>" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Class</label>
                        <select name="class" class="form-select" required>
                            <option value="">Select Class</option>
                            <?php for ($i = 1; $i <= 12; $i++): ?><option value="<?= $i ?>" <?= $class === (string)$i ? 'selected' : '' ?>>Class <?= $i ?></option><?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-12 d-grid"><button class="btn btn-madrasa">Continue to Order</button></div>
                </form>
                <div class="text-center mt-3"><a href="../admin/login.php">Admin Login</a></div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
