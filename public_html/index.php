<?php
require_once __DIR__ . '/../config/db.php';

$books = $pdo->query('SELECT id, book_name, class, price FROM books ORDER BY class, book_name')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Madrasa Book Ordering</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-madrasa navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Madrasa Book Order</a>
        <div class="ms-auto d-flex gap-2">
            <a class="btn btn-light btn-sm" href="order.php">Book Now</a>
            <a class="btn btn-outline-light btn-sm" href="myorders.php">My Orders</a>
            <a class="btn btn-outline-light btn-sm" href="../admin/login.php">Admin</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="p-4 p-md-5 mb-4 rounded-4 text-white bg-madrasa">
        <h1 class="display-6">Order Madrasa Books Easily</h1>
        <p class="lead mb-0">Secure and mobile-friendly portal for students to place book orders online.</p>
    </div>

    <div class="form-section">
        <h4 class="mb-3">Available Books</h4>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>Book Name</th>
                    <th>Class</th>
                    <th>Price</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?= e($book['book_name']) ?></td>
                        <td><?= e($book['class']) ?></td>
                        <td>৳<?= number_format((float) $book['price'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <a href="order.php" class="btn btn-madrasa">Proceed to Book</a>
    </div>
</div>
</body>
</html>
