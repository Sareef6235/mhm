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
        redirect_to('index.php');
    }
    $_SESSION['admin_error'] = 'Invalid credentials';
    redirect_to('index.php');
}

if ($action === 'logout') {
    unset($_SESSION['admin']);
    redirect_to('index.php');
}

if (empty($_SESSION['admin'])) {
    $error = $_SESSION['admin_error'] ?? null;
    unset($_SESSION['admin_error']);
    ?>
    <!doctype html>
    <html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><script src="https://cdn.tailwindcss.com"></script><title>Admin Login</title></head>
    <body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
      <form method="POST" action="?action=login" class="bg-white rounded-2xl shadow-sm p-6 w-full max-w-sm space-y-3">
        <h1 class="font-bold">SKJM Admin</h1>
        <?php if ($error): ?><p class="text-red-600 text-sm"><?= h($error) ?></p><?php endif; ?>
        <input name="username" required placeholder="Username" class="w-full border rounded-xl p-2">
        <input name="password" type="password" required placeholder="Password" class="w-full border rounded-xl p-2">
        <button class="w-full rounded-xl bg-emerald-600 text-white p-2">Login</button>
      </form>
    </body></html>
    <?php
    exit;
}

if ($action === 'add_class' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $className = trim($_POST['class_name'] ?? '');
    if ($className !== '') {
        ClassModel::create($className);
    }
    redirect_to('index.php');
}

if ($action === 'add_student' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = preg_replace('/\D+/', '', $_POST['phone'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $classId = (int)($_POST['class_id'] ?? 0);
    if ($name !== '' && $phone !== '' && $dob !== '' && $classId > 0) {
        StudentModel::create($name, $phone, $dob, $classId, $dob);
    }
    redirect_to('index.php');
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
    redirect_to('index.php');
}

if ($action === 'delete_student') {
    StudentModel::delete((int)($_GET['id'] ?? 0));
    redirect_to('index.php');
}

if ($action === 'import_students' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_FILES['csv_file']['tmp_name'])) {
        $_SESSION['import_result'] = StudentModel::bulkImportFromCsv($_FILES['csv_file']['tmp_name']);
    }
    redirect_to('index.php');
}

if ($action === 'export_records') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="admin-prayer-history.csv"');
    echo PrayerRecordModel::exportCsv();
    exit;
}

$classes = ClassModel::all();
$students = StudentModel::all();
$records = PrayerRecordModel::history();
$dailyLeaders = PrayerRecordModel::leaderboard('day');
$weeklyLeaders = PrayerRecordModel::leaderboard('week');
$monthlyLeaders = PrayerRecordModel::leaderboard('month');
$stats = PrayerRecordModel::adminStats();
$classStats = PrayerRecordModel::classPerformance('month');
$importResult = $_SESSION['import_result'] ?? null;
unset($_SESSION['import_result']);
?>
<!doctype html>
<html>
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>SKJM Admin</title>
</head>
<body class="bg-slate-100 p-4">
<div class="max-w-6xl mx-auto space-y-4">
  <div class="bg-white rounded-2xl shadow-sm p-4 flex justify-between items-center"><h1 class="font-bold">Admin Panel</h1><div class="space-x-3"><a href="?action=export_records" class="text-emerald-700 text-sm">Export Data</a><a href="?action=logout" class="text-red-600 text-sm">Logout</a></div></div>

  <?php if ($importResult): ?>
  <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-3 text-sm">
    Imported: <?= (int)$importResult['inserted'] ?>, Skipped: <?= (int)$importResult['skipped'] ?>
  </div>
  <?php endif; ?>

  <section class="grid md:grid-cols-4 gap-3">
    <div class="bg-white rounded-2xl shadow-sm p-4"><p class="text-xs text-slate-500">Students</p><p class="text-xl font-bold"><?= (int)$stats['total_students'] ?></p></div>
    <div class="bg-white rounded-2xl shadow-sm p-4"><p class="text-xs text-slate-500">Classes</p><p class="text-xl font-bold"><?= (int)$stats['total_classes'] ?></p></div>
    <div class="bg-white rounded-2xl shadow-sm p-4"><p class="text-xs text-slate-500">Prayer Records</p><p class="text-xl font-bold"><?= (int)$stats['total_records'] ?></p></div>
    <div class="bg-white rounded-2xl shadow-sm p-4"><p class="text-xs text-slate-500">Today Points</p><p class="text-xl font-bold"><?= (int)$stats['today_points'] ?></p></div>
  </section>

  <div class="grid md:grid-cols-3 gap-4">
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
      <p class="text-xs text-slate-500">Default password = DOB</p>
    </form>

    <form method="POST" action="?action=import_students" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm p-4 space-y-2">
      <h2 class="font-semibold">Bulk Import (CSV)</h2>
      <p class="text-xs text-slate-500">Fields: name,phone,dob,class</p>
      <input type="file" name="csv_file" accept=".csv,text/csv" class="w-full border rounded-xl p-2 text-sm" required>
      <button class="rounded-xl bg-amber-500 text-white px-3 py-2 text-sm">Import</button>
    </form>
  </div>

  <section class="bg-white rounded-2xl shadow-sm p-4 overflow-auto">
    <h2 class="font-semibold mb-2">Students (Edit/Delete)</h2>
    <table class="w-full text-xs">
      <thead><tr><th class="text-left">Name</th><th>Phone</th><th>DOB</th><th>Class</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($students as $s): ?>
        <tr class="border-t">
          <form method="POST" action="?action=edit_student">
            <td><input name="name" value="<?= h($s['name']) ?>" class="border rounded p-1 w-full"><input type="hidden" name="id" value="<?= (int)$s['id'] ?>"></td>
            <td><input name="phone" value="<?= h($s['phone']) ?>" class="border rounded p-1 w-full"></td>
            <td><input name="dob" type="date" value="<?= h($s['dob']) ?>" class="border rounded p-1 w-full"></td>
            <td>
              <select name="class_id" class="border rounded p-1 w-full">
                <?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= ((int)$s['class_id']===(int)$c['id'])?'selected':'' ?>><?= h($c['class_name']) ?></option><?php endforeach; ?>
              </select>
            </td>
            <td class="space-x-1 whitespace-nowrap">
              <button class="px-2 py-1 bg-emerald-600 text-white rounded">Save</button>
              <a href="?action=delete_student&id=<?= (int)$s['id'] ?>" class="px-2 py-1 bg-red-500 text-white rounded">Delete</a>
            </td>
          </form>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>

  <section class="grid md:grid-cols-3 gap-4" id="leaderboards">
    <div class="bg-white rounded-2xl shadow-sm p-4">
      <h2 class="font-semibold mb-2">ഇന്നത്തെ ടോപ്പർ</h2>
      <?php foreach ($dailyLeaders as $i => $l): ?><div class="text-sm p-2 rounded-xl <?= $i===0?'bg-amber-50':'bg-slate-50' ?> mb-2"><?= ['🥇','🥈','🥉'][$i] ?? '•' ?> <?= h($l['name']) ?> - <?= (int)$l['points'] ?> pt</div><?php endforeach; ?>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-4">
      <h2 class="font-semibold mb-2">ആഴ്ചയിലെ ടോപ്പർ</h2>
      <?php foreach ($weeklyLeaders as $i => $l): ?><div class="text-sm p-2 rounded-xl <?= $i===0?'bg-amber-50':'bg-slate-50' ?> mb-2"><?= ['🥇','🥈','🥉'][$i] ?? '•' ?> <?= h($l['name']) ?> - <?= (int)$l['points'] ?> pt</div><?php endforeach; ?>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-4">
      <h2 class="font-semibold mb-2">മാസത്തിലെ ടോപ്പർ</h2>
      <?php foreach ($monthlyLeaders as $i => $l): ?><div class="text-sm p-2 rounded-xl <?= $i===0?'bg-amber-50':'bg-slate-50' ?> mb-2"><?= ['🥇','🥈','🥉'][$i] ?? '•' ?> <?= h($l['name']) ?> - <?= (int)$l['points'] ?> pt</div><?php endforeach; ?>
    </div>
  </section>

  <section class="grid md:grid-cols-2 gap-4">
    <div class="bg-white rounded-2xl shadow-sm p-4 overflow-auto">
      <h2 class="font-semibold mb-2">Class Performance (Monthly)</h2>
      <table class="w-full text-xs"><thead><tr><th class="text-left">Class</th><th>Students</th><th>Points</th></tr></thead><tbody>
      <?php foreach ($classStats as $cs): ?><tr class="border-t"><td><?= h($cs['class_name']) ?></td><td><?= (int)$cs['student_count'] ?></td><td><?= (int)$cs['points'] ?></td></tr><?php endforeach; ?>
      </tbody></table>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-4 overflow-auto">
      <h2 class="font-semibold mb-2">Recent Prayer Records</h2>
      <table class="w-full text-xs"><thead><tr><th>Student</th><th>Date</th><th>Points</th></tr></thead><tbody>
      <?php foreach (array_slice($records, 0, 20) as $r): ?><tr class="border-t"><td><?= h($r['student_name']) ?></td><td><?= h($r['date']) ?></td><td><?= (int)$r['points'] ?></td></tr><?php endforeach; ?>
      </tbody></table>
    </div>
  </section>
</div>
<script>
setInterval(() => {
  window.location.reload();
}, 120000);
</script>
</body>
</html>
