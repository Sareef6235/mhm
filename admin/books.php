<?php
require_once __DIR__ . '/../config/db.php';
require_admin();

$message = '';
$error = '';
$classes = range(1, 12);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        $bookName = trim($_POST['book_name'] ?? '');
        $class = trim($_POST['class'] ?? '');
        $price = (float)($_POST['price'] ?? 0);

        if ($action === 'add' || $action === 'edit') {
            if ($bookName === '' || !in_array((int)$class, $classes, true) || $price <= 0) {
                $error = 'Book name, class (1-12), and valid price are required.';
            } elseif ($action === 'add') {
                $stmt = $pdo->prepare('INSERT INTO books (book_name, class, price) VALUES (:book_name, :class, :price)');
                $stmt->execute([':book_name' => $bookName, ':class' => (string)$class, ':price' => $price]);
                $message = 'Book added successfully.';
            } else {
                $id = (int)($_POST['id'] ?? 0);
                $stmt = $pdo->prepare('UPDATE books SET book_name = :book_name, class = :class, price = :price WHERE id = :id');
                $stmt->execute([':book_name' => $bookName, ':class' => (string)$class, ':price' => $price, ':id' => $id]);
                $message = 'Book updated successfully.';
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('DELETE FROM books WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $message = 'Book deleted successfully.';
        }
    }
}

$editBook = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM books WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $editBook = $stmt->fetch();
}

$books = $pdo->query('SELECT * FROM books ORDER BY CAST(class AS UNSIGNED), book_name')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Books</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-madrasa navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
        <div class="ms-auto d-flex gap-2">
            <a href="orders.php" class="btn btn-outline-light btn-sm">Orders</a>
            <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="form-section">
                <h5><?= $editBook ? 'Edit Book' : 'Add New Book' ?></h5>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="<?= $editBook ? 'edit' : 'add' ?>">
                    <?php if ($editBook): ?><input type="hidden" name="id" value="<?= (int)$editBook['id'] ?>"><?php endif; ?>
                    <div class="mb-2">
                        <label class="form-label">Book Name</label>
                        <input type="text" name="book_name" class="form-control" value="<?= e($editBook['book_name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Class</label>
                        <select name="class" class="form-select" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $classNumber): ?>
                                <option value="<?= $classNumber ?>" <?= (string)($editBook['class'] ?? '') === (string)$classNumber ? 'selected' : '' ?>>Class <?= $classNumber ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" step="0.01" min="1" name="price" class="form-control" value="<?= e((string)($editBook['price'] ?? '')) ?>" required>
                    </div>
                    <button class="btn btn-madrasa" type="submit"><?= $editBook ? 'Update Book' : 'Add Book' ?></button>
                    <?php if ($editBook): ?><a href="books.php" class="btn btn-secondary">Cancel</a><?php endif; ?>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="form-section">
                <h5>Books List</h5>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Book</th><th>Class</th><th>Price</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php foreach ($books as $book): ?>
                            <tr>
                                <td><?= e($book['book_name']) ?></td>
                                <td>Class <?= e($book['class']) ?></td>
                                <td>₹<?= number_format((float)$book['price'], 2) ?></td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary" href="books.php?edit=<?= (int)$book['id'] ?>">Edit</a>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this book?');">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
