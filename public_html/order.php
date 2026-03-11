<?php
require_once __DIR__ . '/../config/db.php';
require_student();

$student = $_SESSION['student'];
$class = (string)$student['class'];

$textStmt = $pdo->prepare('SELECT id, book_name, price FROM textbooks WHERE class = :class ORDER BY book_name');
$textStmt->execute([':class' => $class]);
$textbooks = $textStmt->fetchAll();

$notebooks = $pdo->query('SELECT id, notebook_type, pages, price FROM notebooks ORDER BY pages')->fetchAll();

$errors = [];
$success = '';
$orderSummary = [];
$finalTotal = 0.0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid request token.';
    }

    $textQty = $_POST['text_qty'] ?? [];
    $noteQty = $_POST['note_qty'] ?? [];

    foreach ($textbooks as $book) {
        $qty = (int)($textQty[$book['id']] ?? 0);
        if ($qty > 0) {
            $line = $qty * (float)$book['price'];
            $orderSummary[] = ['item_type' => 'textbook', 'item_name' => $book['book_name'], 'pages' => null, 'price' => (float)$book['price'], 'quantity' => $qty, 'total' => $line];
            $finalTotal += $line;
        }
    }

    foreach ($notebooks as $nb) {
        $qty = (int)($noteQty[$nb['id']] ?? 0);
        if ($qty > 0) {
            $line = $qty * (float)$nb['price'];
            $orderSummary[] = ['item_type' => 'notebook', 'item_name' => $nb['notebook_type'], 'pages' => (int)$nb['pages'], 'price' => (float)$nb['price'], 'quantity' => $qty, 'total' => $line];
            $finalTotal += $line;
        }
    }

    if (!$orderSummary) {
        $errors[] = 'Please select at least one item quantity.';
    }

    if (!$errors) {
        $insert = $pdo->prepare('INSERT INTO orders (student_name, class, gender, class_number, phone, item_type, item_name, pages, price, quantity, total_price, order_date) VALUES (:student_name, :class, :gender, :class_number, :phone, :item_type, :item_name, :pages, :price, :quantity, :total_price, NOW())');
        $pdo->beginTransaction();
        try {
            foreach ($orderSummary as $item) {
                $insert->execute([
                    ':student_name' => $student['student_name'],
                    ':class' => $student['class'],
                    ':gender' => $student['gender'],
                    ':class_number' => $student['class_number'],
                    ':phone' => $student['phone'],
                    ':item_type' => $item['item_type'],
                    ':item_name' => $item['item_name'],
                    ':pages' => $item['pages'],
                    ':price' => $item['price'],
                    ':quantity' => $item['quantity'],
                    ':total_price' => $item['total'],
                ]);
            }
            $pdo->commit();
            $success = 'Order placed successfully.';
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'Failed to place order.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Books</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-madrasa navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="order.php">Madrasa Book Order</a>
        <div class="ms-auto d-flex gap-2">
            <a class="btn btn-outline-light btn-sm" href="myorders.php">My Orders</a>
            <a class="btn btn-light btn-sm" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="form-section mb-3">
        <h5>Student Details</h5>
        <div class="row g-2">
            <div class="col-md-3"><strong>Name:</strong> <?= e($student['student_name']) ?></div>
            <div class="col-md-2"><strong>Class:</strong> <?= e((string)$student['class']) ?></div>
            <div class="col-md-2"><strong>Gender:</strong> <?= e($student['gender']) ?></div>
            <div class="col-md-2"><strong>Class No:</strong> <?= e((string)$student['class_number']) ?></div>
            <div class="col-md-3"><strong>Phone:</strong> <?= e($student['phone']) ?></div>
        </div>
    </div>

    <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

    <form method="post" id="orderForm">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="form-section mb-3">
            <h5>1️⃣ Text Books (Class <?= e($class) ?> only)</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Book Name</th><th>Price</th><th>Quantity</th><th>Add</th></tr></thead>
                    <tbody>
                    <?php foreach ($textbooks as $book): ?>
                        <tr>
                            <td><?= e($book['book_name']) ?></td>
                            <td>₹<?= number_format((float)$book['price'], 2) ?></td>
                            <td><input type="number" min="0" value="0" class="form-control qty-input" name="text_qty[<?= (int)$book['id'] ?>]" data-price="<?= e((string)$book['price']) ?>"></td>
                            <td><span class="badge text-bg-success">Textbook</span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$textbooks): ?><tr><td colspan="4" class="text-center">No textbooks for your class.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="form-section mb-3">
            <h5>2️⃣ Note Books</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Notebook Type</th><th>Page Count</th><th>Price</th><th>Quantity</th></tr></thead>
                    <tbody>
                    <?php foreach ($notebooks as $nb): ?>
                        <tr>
                            <td><?= e($nb['notebook_type']) ?></td>
                            <td><?= (int)$nb['pages'] ?> pages</td>
                            <td>₹<?= number_format((float)$nb['price'], 2) ?></td>
                            <td><input type="number" min="0" value="0" class="form-control qty-input" name="note_qty[<?= (int)$nb['id'] ?>]" data-price="<?= e((string)$nb['price']) ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="form-section mb-3">
            <h5>Order Summary Before Confirm</h5>
            <p class="mb-2">Final Total Amount: <strong>₹<span id="liveTotal">0.00</span></strong></p>
            <?php if ($orderSummary): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Item</th><th>Type</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
                        <tbody>
                        <?php foreach ($orderSummary as $item): ?>
                            <tr>
                                <td><?= e($item['item_name'] . ($item['pages'] ? ' (' . $item['pages'] . ' pages)' : '')) ?></td>
                                <td><?= e($item['item_type']) ?></td>
                                <td><?= (int)$item['quantity'] ?></td>
                                <td>₹<?= number_format((float)$item['price'], 2) ?></td>
                                <td>₹<?= number_format((float)$item['total'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p><strong>Final Total: ₹<?= number_format($finalTotal, 2) ?></strong></p>
            <?php endif; ?>
            <button type="submit" class="btn btn-madrasa">Confirm Order</button>
        </div>
    </form>
</div>

<script src="../assets/script.js"></script>
</body>
</html>
