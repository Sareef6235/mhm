<?php
session_start();
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/ClassModel.php';
require_once __DIR__ . '/../models/StudentModel.php';
require_once __DIR__ . '/../models/PrayerRecordModel.php';

$config = require __DIR__ . '/../config.php';
$action = $_GET['action'] ?? 'dashboard';

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === $config['admin']['username'] && password_verify($password, $config['admin']['password_hash'])) {
        $_SESSION['admin'] = true;
        redirect_to('/admin');
    }
    $_SESSION['login_error'] = 'Invalid credentials';
    redirect_to('/admin');
}

if ($action === 'logout') {
    session_destroy();
    redirect_to('/admin');
}

if (empty($_SESSION['admin'])) {
    $error = $_SESSION['login_error'] ?? null;
    unset($_SESSION['login_error']);
    ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Admin Login</title>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
  <form method="POST" action="?action=login" class="bg-white rounded-2xl shadow-sm p-6 w-full max-w-sm space-y-3">
    <h1 class="font-bold">Admin Login</h1>
    <?php if ($error): ?><p class="text-red-600 text-sm"><?= h($error) ?></p><?php endif; ?>
    <input name="username" class="w-full border rounded-xl p-2" placeholder="username" required>
    <input name="password" type="password" class="w-full border rounded-xl p-2" placeholder="password" required>
    <button class="w-full rounded-xl bg-emerald-600 text-white p-2">Login</button>
  </form>
</body>
</html>
<?php
    exit;
}

if ($action === 'add_class' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['class_name'] ?? '');
    if ($name !== '') ClassModel::create($name);
    redirect_to('/admin');
}
if ($action === 'add_student' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $classId = (int)($_POST['class_id'] ?? 0);
    if ($name !== '' && $classId > 0) StudentModel::create($name, $classId);
    redirect_to('/admin');
}
if ($action === 'edit_student' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    StudentModel::update((int)$_POST['id'], trim($_POST['name']), (int)$_POST['class_id']);
    redirect_to('/admin');
}
if ($action === 'delete_student') {
    StudentModel::delete((int)($_GET['id'] ?? 0));
    redirect_to('/admin');
}

$classes = ClassModel::all();
$students = StudentModel::all();
$reports = PrayerRecordModel::history();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Admin</title>
</head>
<body class="bg-slate-100 p-4">
<div class="max-w-4xl mx-auto space-y-4">
  <div class="flex justify-between bg-white rounded-2xl shadow-sm p-4"><h1 class="font-bold">Admin Panel</h1><a href="?action=logout" class="text-sm text-red-500">Logout</a></div>
  <div class="grid md:grid-cols-2 gap-4">
    <form method="POST" action="?action=add_class" class="bg-white rounded-2xl shadow-sm p-4 space-y-2">
      <h2 class="font-semibold">Add Class</h2><input name="class_name" class="w-full border rounded-xl p-2" required>
      <button class="rounded-xl bg-emerald-600 text-white px-3 py-2 text-sm">Save</button>
    </form>
    <form method="POST" action="?action=add_student" class="bg-white rounded-2xl shadow-sm p-4 space-y-2">
      <h2 class="font-semibold">Add Student</h2><input name="name" class="w-full border rounded-xl p-2" required>
      <select name="class_id" class="w-full border rounded-xl p-2"><?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>"><?= h($c['class_name']) ?></option><?php endforeach; ?></select>
      <button class="rounded-xl bg-emerald-600 text-white px-3 py-2 text-sm">Save</button>
    </form>
  </div>
  <div class="bg-white rounded-2xl shadow-sm p-4 overflow-auto">
    <h2 class="font-semibold mb-2">Students</h2>
    <table class="w-full text-sm"><thead><tr><th class="text-left">Name</th><th class="text-left">Class</th><th>Actions</th></tr></thead><tbody>
      <?php foreach ($students as $s): ?>
      <tr class="border-t"><td><?= h($s['name']) ?></td><td><?= h($s['class_name']) ?></td><td class="text-right"><a class="text-red-500" href="?action=delete_student&id=<?= $s['id'] ?>">Delete</a></td></tr>
      <?php endforeach; ?>
    </tbody></table>
  </div>
  <div class="bg-white rounded-2xl shadow-sm p-4 overflow-auto">
    <h2 class="font-semibold mb-2">Reports</h2>
    <table class="w-full text-xs"><thead><tr><th>Student</th><th>Date</th><th>Points</th><th>Salawat</th></tr></thead><tbody>
      <?php foreach ($reports as $r): ?><tr class="border-t"><td><?= h($r['student_name']) ?></td><td><?= h($r['date']) ?></td><td><?= h((string)$r['total_points']) ?></td><td><?= h((string)$r['salawat']) ?></td></tr><?php endforeach; ?>
    </tbody></table>
  </div>
</div>
</body>
</html>
