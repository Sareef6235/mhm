<?php
require_once __DIR__ . '/../config/db.php';

$classes = range(1, 12);
$stmt = $pdo->query('SELECT id, book_name, class, price FROM books ORDER BY CAST(class AS UNSIGNED), book_name');
$allBooks = $stmt->fetchAll();

$booksByClass = [];
foreach ($classes as $classNumber) {
    $booksByClass[(string)$classNumber] = [];
}

foreach ($allBooks as $book) {
    $bookClass = (string)$book['class'];
    if (array_key_exists($bookClass, $booksByClass)) {
        $booksByClass[$bookClass][] = $book;
    }
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid request token. Please refresh and try again.';
    }

    $studentName = trim($_POST['student_name'] ?? '');
    $class = trim($_POST['class'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bookId = (int)($_POST['book_id'] ?? 0);
    $qty = (int)($_POST['quantities'][$bookId] ?? 0);

    if ($studentName === '' || $class === '' || $phone === '') {
        $errors[] = 'Student name, class and phone number are required.';
    }

    if (!preg_match('/^[0-9+\-\s]{8,20}$/', $phone)) {
        $errors[] = 'Enter a valid phone number.';
    }

    if ($bookId <= 0 || $qty <= 0) {
        $errors[] = 'Please select a valid quantity and click Book on a book row.';
    }

    if (!$errors) {
        $bookStmt = $pdo->prepare('SELECT book_name, class, price FROM books WHERE id = :id LIMIT 1');
        $bookStmt->execute([':id' => $bookId]);
        $book = $bookStmt->fetch();

        if (!$book) {
            $errors[] = 'Selected book was not found.';
        } else {
            $price = (float)$book['price'];
            $totalPrice = $price * $qty;

            $insert = $pdo->prepare(
                'INSERT INTO orders (student_name, class, phone, book_name, price, quantity, total_price, order_date)
                 VALUES (:student_name, :class, :phone, :book_name, :price, :quantity, :total_price, NOW())'
            );

            $insert->execute([
                ':student_name' => $studentName,
                ':class' => $class,
                ':phone' => $phone,
                ':book_name' => $book['book_name'],
                ':price' => $price,
                ':quantity' => $qty,
                ':total_price' => $totalPrice,
            ]);

            $success = 'Book ordered successfully.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Book Madrasa Books</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-madrasa navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Madrasa Book Order</a>
        <div class="ms-auto d-flex gap-2">
            <a class="btn btn-outline-light btn-sm" href="index.php">Home</a>
            <a class="btn btn-outline-light btn-sm" href="myorders.php">My Orders</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <?php if ($errors): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

    <form method="post" class="form-section">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="row g-3 mb-4">
            <div class="col-md-4"><label class="form-label">Student Name</label><input type="text" name="student_name" class="form-control" required></div>
            <div class="col-md-4">
                <label class="form-label">Student Class</label>
                <select name="class" class="form-select" required>
                    <option value="">Select Class</option>
                    <?php foreach ($classes as $classNumber): ?>
                        <option value="<?= $classNumber ?>">Class <?= $classNumber ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4"><label class="form-label">Phone Number</label><input type="text" name="phone" class="form-control" required></div>
        </div>

        <h5 class="mb-3">Select Class & Book</h5>
        <div class="accordion" id="classAccordion">
            <?php foreach ($classes as $index => $classNumber): ?>
                <?php $classKey = (string)$classNumber; ?>
                <div class="accordion-item class-card mb-2">
                    <h2 class="accordion-header" id="heading<?= $classNumber ?>">
                        <button class="accordion-button <?= $index === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $classNumber ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $classNumber ?>">
                            Class <?= $classNumber ?>
                        </button>
                    </h2>
                    <div id="collapse<?= $classNumber ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" data-bs-parent="#classAccordion">
                        <div class="accordion-body">
                            <?php if (!empty($booksByClass[$classKey])): ?>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead><tr><th>Book Name</th><th>Price</th><th>Quantity</th><th>Book Button</th></tr></thead>
                                        <tbody>
                                        <?php foreach ($booksByClass[$classKey] as $book): ?>
                                            <tr>
                                                <td><?= e($book['book_name']) ?></td>
                                                <td>₹<?= number_format((float)$book['price'], 2) ?></td>
                                                <td width="140"><input type="number" min="1" value="1" name="quantities[<?= (int)$book['id'] ?>]" class="form-control"></td>
                                                <td>
                                                    <button type="submit" name="book_id" value="<?= (int)$book['id'] ?>" class="btn btn-madrasa btn-sm">Book</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-muted">No books available for Class <?= $classNumber ?>.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
