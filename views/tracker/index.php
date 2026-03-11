<header class="bg-white rounded-2xl shadow-sm p-4">
  <h2 class="text-base font-bold">📿 Prayer Tracker</h2>
</header>

<form method="GET" class="grid grid-cols-2 gap-2">
  <input type="hidden" name="page" value="tracker">
  <?php foreach ($classes as $c): ?>
    <button name="class_id" value="<?= $c['id'] ?>" class="rounded-2xl bg-white p-3 text-sm shadow-sm <?= ((int)$selectedClass===(int)$c['id'])?'ring-2 ring-emerald-500':'' ?>">
      <?= h($c['class_name']) ?>
    </button>
  <?php endforeach; ?>
</form>

<form method="GET" class="bg-white rounded-2xl shadow-sm p-3">
  <input type="hidden" name="page" value="tracker">
  <input type="hidden" name="class_id" value="<?= (int)$selectedClass ?>">
  <input name="search" value="<?= h($search) ?>" class="w-full border rounded-xl p-2 text-sm" placeholder="Search student by name or phone">
</form>

<section class="space-y-2">
  <?php foreach ($students as $s): ?>
    <a href="<?= app_url('index.php?page=record&student_id=' . (int)$s['id']) ?>" class="block bg-white rounded-2xl shadow-sm p-3">
      <div class="flex justify-between">
        <div>
          <p class="text-sm font-semibold"><?= h($s['name']) ?></p>
          <p class="text-xs text-slate-500"><?= h($s['class_name']) ?></p>
        </div>
        <p class="text-xs text-slate-500"><?= h($s['phone']) ?></p>
      </div>
    </a>
  <?php endforeach; ?>
  <?php if (!$students): ?><p class="text-xs text-slate-500">No students found.</p><?php endif; ?>
</section>
