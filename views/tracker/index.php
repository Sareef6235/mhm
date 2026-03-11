<header class="bg-white rounded-2xl shadow-sm p-4">
  <h2 class="text-base font-bold">📿 നമസ്കാരം രേഖപ്പെടുത്തുക</h2>
</header>

<form method="GET" class="grid grid-cols-2 gap-3">
  <input type="hidden" name="page" value="tracker">
  <?php foreach ($classes as $c): ?>
    <button name="class_id" value="<?= $c['id'] ?>" class="rounded-2xl shadow-sm p-4 bg-white text-left hover:scale-[1.02] active:scale-95 transition <?= $selectedClass === (int)$c['id'] ? 'ring-2 ring-emerald-500' : '' ?>">
      <p class="text-sm font-semibold"><?= h($c['class_name']) ?></p>
    </button>
  <?php endforeach; ?>
</form>

<form method="GET" class="bg-white rounded-2xl shadow-sm p-3">
  <input type="hidden" name="page" value="tracker">
  <input type="hidden" name="class_id" value="<?= (int)$selectedClass ?>">
  <input name="search" value="<?= h((string)$search) ?>" placeholder="Search students" class="w-full border rounded-xl p-2 text-sm">
</form>

<section class="space-y-2">
  <?php foreach ($students as $s): ?>
    <a href="index.php?page=record&student_id=<?= $s['id'] ?>" class="block bg-white rounded-2xl shadow-sm p-3 hover:scale-[1.01] transition">
      <p class="text-sm font-semibold"><?= h($s['name']) ?></p>
      <p class="text-xs text-slate-500"><?= h($s['class_name']) ?></p>
    </a>
  <?php endforeach; ?>
  <?php if (!$students): ?><p class="text-xs text-slate-500">വിദ്യാർത്ഥികൾ ലഭ്യമല്ല.</p><?php endif; ?>
</section>
