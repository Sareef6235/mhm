<?php
require_once __DIR__ . '/../config/db.php';

if (!empty($_SESSION['student'])) {
    header('Location: order.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid request token.';
    }

    $studentName = trim($_POST['student_name'] ?? '');
    $class = trim($_POST['class'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $classNumber = trim($_POST['class_number'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($studentName === '' || $class === '' || $gender === '' || $classNumber === '' || $phone === '') {
        $errors[] = 'All fields are required.';
    }

    if (!in_array((int)$class, range(1, 12), true)) {
        $errors[] = 'Please select a valid class.';
    }

    if (!in_array($gender, ['Male', 'Female'], true)) {
        $errors[] = 'Please select a valid gender.';
    }

    if (!preg_match('/^\d{1,5}$/', $classNumber)) {
        $errors[] = 'Class number must be numeric.';
    }

    if (!preg_match('/^[0-9+\-\s]{8,20}$/', $phone)) {
        $errors[] = 'Enter a valid phone number.';
    }

    if (!$errors) {
        $existsStmt = $pdo->prepare('SELECT id FROM students WHERE class = :class AND gender = :gender AND class_number = :class_number LIMIT 1');
        $existsStmt->execute([
            ':class' => $class,
            ':gender' => $gender,
            ':class_number' => $classNumber,
        ]);
        if ($existsStmt->fetch()) {
            $errors[] = "This class number already exists for {$gender} students in this class.";
        }
    }

    if (!$errors) {
        $insert = $pdo->prepare('INSERT INTO students (student_name, class, gender, class_number, phone, created_at) VALUES (:student_name, :class, :gender, :class_number, :phone, NOW())');
        $insert->execute([
            ':student_name' => $studentName,
            ':class' => $class,
            ':gender' => $gender,
            ':class_number' => $classNumber,
            ':phone' => $phone,
        ]);

        $_SESSION['student'] = [
            'id' => (int)$pdo->lastInsertId(),
            'student_name' => $studentName,
            'class' => $class,
            'gender' => $gender,
            'class_number' => $classNumber,
            'phone' => $phone,
        ];

        header('Location: order.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="login-page">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="form-section">
                <h3 class="mb-3 text-center">Student Login</h3>
                <?php if ($errors): ?>
                    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>

                <form method="post" class="row g-3">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="col-12"><label class="form-label">Student Name</label><input class="form-control" name="student_name" required></div>
                    <div class="col-md-6">
                        <label class="form-label">Class</label>
                        <select class="form-select" name="class" required>
                            <option value="">Select Class</option>
                            <?php for ($i = 1; $i <= 12; $i++): ?><option value="<?= $i ?>">Class <?= $i ?></option><?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gender</label>
                        <select class="form-select" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Class Number</label><input class="form-control" name="class_number" type="number" min="1" required></div>
                    <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" required></div>
                    <div class="col-12 d-grid"><button class="btn btn-madrasa">Continue to Order</button></div>
                </form>
                <div class="text-center mt-3"><a href="../admin/login.php">Admin Login</a></div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
