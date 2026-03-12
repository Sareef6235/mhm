<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../core/View.php';

if (admin_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $admin = AdminModel::findByUsername($username);
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: dashboard.php');
        exit;
    }

    $error = 'Invalid username or password';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Admin Login</title>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
  <form method="POST" class="bg-white rounded-2xl shadow-sm p-6 w-full max-w-sm space-y-3">
    <h1 class="font-bold text-lg">Admin Login</h1>
    <p class="text-xs text-slate-500">Username: admin · Password: Admin@123</p>
    <?php if ($error): ?><p class="text-red-600 text-sm"><?= h($error) ?></p><?php endif; ?>
    <input name="username" required placeholder="Username" class="w-full border rounded-xl p-2">
    <input name="password" type="password" required placeholder="Password" class="w-full border rounded-xl p-2">
    <button class="w-full rounded-xl bg-emerald-600 text-white p-2">Login</button>
  </form>
</body></html>
