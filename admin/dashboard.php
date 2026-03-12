<?php
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/ClassModel.php';
require_once __DIR__ . '/../models/StudentModel.php';
require_once __DIR__ . '/../models/PrayerRecordModel.php';

$action = $_GET['action'] ?? '';

if ($action === 'add_class' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $className = trim($_POST['class_name'] ?? '');
    if ($className !== '') {
        ClassModel::create($className);
    }
    header('Location: dashboard.php');
    exit;
}

if ($action === 'add_student' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = preg_replace('/\D+/', '', $_POST['phone'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $classId = (int)($_POST['class_id'] ?? 0);
    if ($name !== '' && $phone !== '' && $dob !== '' && $classId > 0) {
        StudentModel::create($name, $phone, $dob, $classId, $dob);
    }
    header('Location: dashboard.php');
    exit;
}

if ($action === 'edit_student' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $phone = preg_replace('/\D+/', '', $_POST['phone'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $classId = (int)($_POST['class_id'] ?? 0);
    if ($id > 0 && $name !== '' && $phone !== '' && $dob !== '' && $classId > 0) {
        StudentModel::update($id, $name, $phone, $dob, $classId);
    }
    header('Location: dashboard.php');
    exit;
}

if ($action === 'delete_student') {
    StudentModel::delete((int)($_GET['id'] ?? 0));
    header('Location: dashboard.php');
    exit;
}

if ($action === 'export') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="admin-prayer-history.csv"');
    echo PrayerRecordModel::exportCsv();
    exit;
}

$classes = ClassModel::all();
$students = StudentModel::all();
$stats = PrayerRecordModel::adminStats();
$dailyLeaders = PrayerRecordModel::leaderboard('day');
$weeklyLeaders = PrayerRecordModel::leaderboard('week');
$monthlyLeaders = PrayerRecordModel::leaderboard('month');
?>
<!doctype html>
<html>
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Admin Dashboard</title>
</head>
<body class="bg-slate-100 p-4">
<div class="max-w-6xl mx-auto space-y-4">
  <div class="bg-white rounded-2xl shadow-sm p-4 flex justify-between items-center"><h1 class="font-bold">Admin Dashboard</h1><div class="space-x-3"><a href="?action=export" class="text-emerald-700 text-sm">Export CSV</a><a href="logout.php" class="text-red-600 text-sm">Logout</a></div></div>

  <section class="grid md:grid-cols-4 gap-3">
    <div class="bg-white rounded-2xl shadow-sm p-4"><p class="text-xs text-slate-500">Students</p><p class="text-xl font-bold"><?= (int)$stats['total_students'] ?></p></div>
    <div class="bg-white rounded-2xl shadow-sm p-4"><p class="text-xs text-slate-500">Classes</p><p class="text-xl font-bold"><?= (int)$stats['total_classes'] ?></p></div>
    <div class="bg-white rounded-2xl shadow-sm p-4"><p class="text-xs text-slate-500">Prayer Records</p><p class="text-xl font-bold"><?= (int)$stats['total_records'] ?></p></div>
    <div class="bg-white rounded-2xl shadow-sm p-4"><p class="text-xs text-slate-500">Today Points</p><p class="text-xl font-bold"><?= (int)$stats['today_points'] ?></p></div>
  </section>

  <div class="grid md:grid-cols-2 gap-4">
    <form method="POST" action="?action=add_class" class="bg-white rounded-2xl shadow-sm p-4 space-y-2">
      <h2 class="font-semibold">Add Class</h2>
      <input name="class_name" class="w-full border rounded-xl p-2" placeholder="Class name" required>
      <button class="rounded-xl bg-emerald-600 text-white px-3 py-2 text-sm">Save</button>
    </form>

    <form method="POST" action="?action=add_student" class="bg-white rounded-2xl shadow-sm p-4 space-y-2">
      <h2 class="font-semibold">Add Student</h2>
      <input name="name" class="w-full border rounded-xl p-2" placeholder="Name" required>
      <input name="phone" class="w-full border rounded-xl p-2" placeholder="Phone" required>
      <input name="dob" type="date" class="w-full border rounded-xl p-2" required>
      <select name="class_id" class="w-full border rounded-xl p-2" required><?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>"><?= h($c['class_name']) ?></option><?php endforeach; ?></select>
      <button class="rounded-xl bg-emerald-600 text-white px-3 py-2 text-sm">Save</button>
    </form>
  </div>

  <section class="bg-white rounded-2xl shadow-sm p-4 overflow-auto">
    <h2 class="font-semibold mb-2">Students</h2>
    <table class="w-full text-xs"><thead><tr><th>Name</th><th>Phone</th><th>DOB</th><th>Class</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($students as $s): ?><tr class="border-t"><form method="POST" action="?action=edit_student"><td><input name="name" value="<?= h($s['name']) ?>" class="border rounded p-1 w-full"><input type="hidden" name="id" value="<?= (int)$s['id'] ?>"></td><td><input name="phone" value="<?= h($s['phone']) ?>" class="border rounded p-1 w-full"></td><td><input name="dob" type="date" value="<?= h($s['dob']) ?>" class="border rounded p-1 w-full"></td><td><select name="class_id" class="border rounded p-1 w-full"><?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= ((int)$s['class_id']===(int)$c['id'])?'selected':'' ?>><?= h($c['class_name']) ?></option><?php endforeach; ?></select></td><td class="space-x-1"><button class="px-2 py-1 bg-emerald-600 text-white rounded">Save</button><a href="?action=delete_student&id=<?= (int)$s['id'] ?>" class="px-2 py-1 bg-red-500 text-white rounded">Delete</a></td></form></tr><?php endforeach; ?>
    </tbody></table>
  </section>

  <section class="grid md:grid-cols-3 gap-4">
    <div class="bg-white rounded-2xl shadow-sm p-4"><h3 class="font-semibold mb-2">ഇന്നത്തെ ടോപ്പർ</h3><?php foreach ($dailyLeaders as $i => $l): ?><p class="text-sm mb-1"><?= ['🥇','🥈','🥉'][$i] ?? '•' ?> <?= h($l['name']) ?> - <?= (int)$l['points'] ?></p><?php endforeach; ?></div>
    <div class="bg-white rounded-2xl shadow-sm p-4"><h3 class="font-semibold mb-2">ആഴ്ചയിലെ</h3><?php foreach ($weeklyLeaders as $i => $l): ?><p class="text-sm mb-1"><?= ['🥇','🥈','🥉'][$i] ?? '•' ?> <?= h($l['name']) ?> - <?= (int)$l['points'] ?></p><?php endforeach; ?></div>
    <div class="bg-white rounded-2xl shadow-sm p-4"><h3 class="font-semibold mb-2">മാസത്തിലെ</h3><?php foreach ($monthlyLeaders as $i => $l): ?><p class="text-sm mb-1"><?= ['🥇','🥈','🥉'][$i] ?? '•' ?> <?= h($l['name']) ?> - <?= (int)$l['points'] ?></p><?php endforeach; ?></div>
  </section>
</div>
</body>
</html>
