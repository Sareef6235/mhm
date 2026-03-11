<?php
require_once __DIR__ . '/../config/db.php';

$books = $pdo->query('SELECT id, book_name, class, price FROM books ORDER BY class, book_name')->fetchAll();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid request token. Please refresh and try again.';
    }

    $studentName = trim($_POST['student_name'] ?? '');
    $class = trim($_POST['class'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $quantities = $_POST['quantities'] ?? [];

    if ($studentName === '' || $class === '' || $phone === '') {
        $errors[] = 'Student name, class and phone number are required.';
    }

    if (!preg_match('/^[0-9+\-\s]{8,20}$/', $phone)) {
        $errors[] = 'Enter a valid phone number.';
    }

    $selected = [];
    foreach ($books as $book) {
        $qty = (int)($quantities[$book['id']] ?? 0);
        if ($qty > 0) {
            $selected[] = [$book, $qty];
        }
    }

    if (count($selected) === 0) {
        $errors[] = 'Please select at least one book quantity.';
    }

    if (!$errors) {
        $insert = $pdo->prepare(
            'INSERT INTO orders (student_name, class, phone, book_name, price, quantity, total_price, order_date)
             VALUES (:student_name, :class, :phone, :book_name, :price, :quantity, :total_price, NOW())'
        );

        try {
            $pdo->beginTransaction();
            foreach ($selected as [$book, $qty]) {
                $price = (float)$book['price'];
                $totalPrice = $price * $qty;

                $insert->execute([
                    ':student_name' => $studentName,
                    ':class' => $class,
                    ':phone' => $phone,
                    ':book_name' => $book['book_name'],
                    ':price' => $price,
                    ':quantity' => $qty,
                    ':total_price' => $totalPrice,
                ]);
            }
            $pdo->commit();
            $success = 'Order placed successfully. You can check My Orders using your phone number.';
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'Could not save your order. Please try again.';
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
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <form method="post" class="form-section">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Student Name</label>
                <input type="text" name="student_name" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Class</label>
                <input type="text" name="class" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control" required>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>Book Name</th>
                    <th>Class</th>
                    <th>Price</th>
                    <th width="150">Quantity</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?= e($book['book_name']) ?></td>
                        <td><?= e($book['class']) ?></td>
                        <td><?= number_format((float)$book['price'], 2) ?></td>
                        <td>
                            <input type="number" min="0" value="0" name="quantities[<?= (int)$book['id'] ?>]"
                                   class="form-control quantity-input" data-price="<?= e((string)$book['price']) ?>">
                        </td>
                        <td>৳<span class="row-total">0.00</span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3">
            <h5 class="mb-0">Grand Total: ৳<span id="grandTotal">0.00</span></h5>
            <input type="hidden" id="grandTotalInput" name="calculated_total" value="0.00">
            <button type="submit" class="btn btn-madrasa">Book Now</button>
        </div>
    </form>
</div>

<script src="../assets/script.js"></script>
</body>
</html>
