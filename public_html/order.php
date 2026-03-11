<?php
require_once __DIR__ . '/../config/db.php';

$classes = range(1, 12);
$studentName = trim($_POST['student_name'] ?? ($_GET['student_name'] ?? ''));
$selectedClass = trim($_POST['class'] ?? ($_GET['class'] ?? ''));
$gender = trim($_POST['gender'] ?? '');
$classNumber = trim($_POST['class_number'] ?? '');

if ($studentName === '' || $selectedClass === '' || !in_array((int)$selectedClass, $classes, true)) {
    header('Location: index.php');
    exit;
}

$textStmt = $pdo->prepare('SELECT id, book_name, price FROM textbooks WHERE class = :class ORDER BY book_name');
$textStmt->execute([':class' => $selectedClass]);
$textbooks = $textStmt->fetchAll();
$notebooks = $pdo->query('SELECT id, notebook_type, pages, price FROM notebooks ORDER BY pages')->fetchAll();

$errors = [];
$success = '';
$orderSummary = [];
$finalTotal = 0.0;
$showSummary = false;

$textQty = $_POST['text_qty'] ?? [];
$noteQty = $_POST['note_qty'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['review_order']) || isset($_POST['confirm_order']))) {
    if (!verify_csrf()) {
        $errors[] = 'Invalid request token.';
    }

    if (!in_array($gender, ['Male', 'Female'], true)) {
        $errors[] = 'Please select a valid gender.';
    }

    if (!preg_match('/^\d{1,5}$/', $classNumber)) {
        $errors[] = 'Class Number must be numeric.';
    }

    foreach ($textbooks as $book) {
        $qty = (int)($textQty[$book['id']] ?? 0);
        if ($qty > 0) {
            $line = $qty * (float)$book['price'];
            $orderSummary[] = [
                'item_type' => 'textbook',
                'item_name' => $book['book_name'],
                'pages' => null,
                'price' => (float)$book['price'],
                'quantity' => $qty,
                'total' => $line,
            ];
            $finalTotal += $line;
        }
    }

    foreach ($notebooks as $nb) {
        $qty = (int)($noteQty[$nb['id']] ?? 0);
        if ($qty > 0) {
            $line = $qty * (float)$nb['price'];
            $orderSummary[] = [
                'item_type' => 'notebook',
                'item_name' => $nb['notebook_type'],
                'pages' => (int)$nb['pages'],
                'price' => (float)$nb['price'],
                'quantity' => $qty,
                'total' => $line,
            ];
            $finalTotal += $line;
        }
    }

    if (!$orderSummary) {
        $errors[] = 'Please select at least one quantity.';
    }

    if (!$errors) {
        $showSummary = true;
    }

    if (!$errors && isset($_POST['confirm_order'])) {
        $existsStmt = $pdo->prepare('SELECT id FROM students WHERE class = :class AND gender = :gender AND class_number = :class_number LIMIT 1');
        $existsStmt->execute([
            ':class' => $selectedClass,
            ':gender' => $gender,
            ':class_number' => $classNumber,
        ]);

        if ($existsStmt->fetch()) {
            $errors[] = "This class number already exists for {$gender} students in this class.";
            $showSummary = true;
        } else {
            $insertStudent = $pdo->prepare('INSERT INTO students (student_name, class, gender, class_number, created_at) VALUES (:student_name, :class, :gender, :class_number, NOW())');
            $insertOrder = $pdo->prepare('INSERT INTO orders (student_name, class, gender, class_number, item_type, item_name, pages, price, quantity, total_price, order_date) VALUES (:student_name, :class, :gender, :class_number, :item_type, :item_name, :pages, :price, :quantity, :total_price, NOW())');

            $pdo->beginTransaction();
            try {
                $insertStudent->execute([
                    ':student_name' => $studentName,
                    ':class' => $selectedClass,
                    ':gender' => $gender,
                    ':class_number' => $classNumber,
                ]);

                foreach ($orderSummary as $item) {
                    $insertOrder->execute([
                        ':student_name' => $studentName,
                        ':class' => $selectedClass,
                        ':gender' => $gender,
                        ':class_number' => $classNumber,
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
                $textQty = $noteQty = [];
                $orderSummary = [];
                $finalTotal = 0.0;
                $showSummary = false;
            } catch (Throwable $e) {
                $pdo->rollBack();
                $errors[] = 'Failed to save order. ' . $e->getMessage();
            }
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
        <a class="navbar-brand" href="index.php">Madrasa Book Order</a>
        <div class="ms-auto d-flex gap-2">
            <a class="btn btn-outline-light btn-sm" href="myorders.php">My Orders</a>
            <a class="btn btn-light btn-sm" href="../admin/login.php">Admin</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

    <form method="post" id="orderForm">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="student_name" value="<?= e($studentName) ?>">
        <input type="hidden" name="class" value="<?= e($selectedClass) ?>">

        <div class="form-section mb-3">
            <h5>Student Info</h5>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Student Name</label><input class="form-control" value="<?= e($studentName) ?>" readonly></div>
                <div class="col-md-2"><label class="form-label">Class</label><input class="form-control" value="<?= e($selectedClass) ?>" readonly></div>
                <div class="col-md-3">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select" required>
                        <option value="">Select</option>
                        <option value="Male" <?= $gender === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $gender === 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label">Class Number</label><input type="number" min="1" name="class_number" class="form-control" value="<?= e($classNumber) ?>" required></div>
            </div>
        </div>

        <div class="form-section mb-3">
            <h5>1️⃣ TEXT BOOKS (Class <?= e($selectedClass) ?>)</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Book Name</th><th>Price</th><th>Quantity</th></tr></thead>
                    <tbody>
                    <?php foreach ($textbooks as $book): ?>
                        <tr>
                            <td><?= e($book['book_name']) ?></td>
                            <td>₹<?= number_format((float)$book['price'], 2) ?></td>
                            <td><input type="number" min="0" name="text_qty[<?= (int)$book['id'] ?>]" value="<?= e((string)($textQty[$book['id']] ?? 0)) ?>" data-price="<?= e((string)$book['price']) ?>" class="form-control qty-input"></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$textbooks): ?><tr><td colspan="3" class="text-center">No textbooks for this class.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="form-section mb-3">
            <h5>2️⃣ NOTE BOOKS</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Notebook Type</th><th>Pages</th><th>Price</th><th>Quantity</th></tr></thead>
                    <tbody>
                    <?php foreach ($notebooks as $nb): ?>
                        <tr>
                            <td><?= e($nb['notebook_type']) ?></td>
                            <td><?= (int)$nb['pages'] ?></td>
                            <td>₹<?= number_format((float)$nb['price'], 2) ?></td>
                            <td><input type="number" min="0" name="note_qty[<?= (int)$nb['id'] ?>]" value="<?= e((string)($noteQty[$nb['id']] ?? 0)) ?>" data-price="<?= e((string)$nb['price']) ?>" class="form-control qty-input"></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="form-section">
            <h5>Order Summary Before Confirm</h5>
            <p>Final Total Amount: <strong>₹<span id="liveTotal">0.00</span></strong></p>

            <?php if ($showSummary): ?>
                <p><strong><?= e($studentName) ?></strong> | Class <?= e($selectedClass) ?> | <?= e($gender) ?> | Class No: <?= e($classNumber) ?></p>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Item Name</th><th>Quantity</th><th>Price</th><th>Total</th></tr></thead>
                        <tbody>
                        <?php foreach ($orderSummary as $item): ?>
                            <tr>
                                <td><?= e($item['item_name'] . ($item['pages'] ? ' (' . $item['pages'] . ' pages)' : '')) ?></td>
                                <td><?= (int)$item['quantity'] ?></td>
                                <td>₹<?= number_format((float)$item['price'], 2) ?></td>
                                <td>₹<?= number_format((float)$item['total'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p><strong>Final Total Amount: ₹<?= number_format($finalTotal, 2) ?></strong></p>
                <button type="submit" name="confirm_order" value="1" class="btn btn-madrasa">Confirm Order</button>
            <?php else: ?>
                <button type="submit" name="review_order" value="1" class="btn btn-madrasa">Review Order</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<script src="../assets/script.js"></script>
<?php render_site_footer(); ?>
</body>
</html>
