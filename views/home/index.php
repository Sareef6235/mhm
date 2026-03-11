<header class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white rounded-2xl p-5 shadow-sm">
  <h1 class="text-xl font-bold">🕌 SKJM Prayer Tracker</h1>
  <p class="text-xs text-emerald-100">Daily Prayer & Study Tracker</p>
</header>

<section class="bg-white rounded-2xl shadow-sm p-4 text-sm">
  <p><span class="text-slate-500">Student:</span> <strong><?= h($student['name']) ?></strong></p>
  <p><span class="text-slate-500">Class:</span> <?= h($student['class_name']) ?></p>
  <p><span class="text-slate-500">Today:</span> <?= date('Y-m-d') ?></p>
</section>

<section class="bg-white rounded-2xl shadow-sm p-4">
  <div class="flex justify-between items-center">
    <h2 class="text-sm font-semibold">Today's Progress</h2>
    <span class="text-xs font-semibold text-emerald-700"><?= (int)($today['points'] ?? 0) ?> pt</span>
  </div>
  <?php $done = (int)($today['subah'] ?? 0)+(int)($today['dhuhr'] ?? 0)+(int)($today['asr'] ?? 0)+(int)($today['maghrib'] ?? 0)+(int)($today['isha'] ?? 0); $pct = (int)(($done/5)*100); ?>
  <div class="mt-2 h-2 bg-slate-200 rounded-full overflow-hidden"><div class="h-full bg-emerald-500" style="width: <?= $pct ?>%"></div></div>
  <p class="text-xs text-slate-500 mt-2">Completed <?= $done ?>/5 prayers · Salawat <?= (int)($today['salawat'] ?? 0) ?></p>
</section>

<form method="GET" class="bg-white rounded-2xl shadow-sm p-4">
  <input type="hidden" name="page" value="home">
  <select name="class_id" class="w-full rounded-xl border p-2 text-sm" onchange="this.form.submit()">
    <option value="">All classes</option>
    <?php foreach ($classes as $c): ?>
      <option value="<?= $c['id'] ?>" <?= ((int)$classId === (int)$c['id']) ? 'selected' : '' ?>><?= h($c['class_name']) ?></option>
    <?php endforeach; ?>
  </select>
</form>

<?php $sections = ['ഇന്നത്തെ ടോപ്പർ' => $daily, 'ആഴ്ചയിലെ ടോപ്പർ' => $week, 'മാസത്തിലെ ടോപ്പർ' => $month]; $medals = ['🥇','🥈','🥉']; ?>
<?php foreach ($sections as $title => $items): ?>
<section class="bg-white rounded-2xl shadow-sm p-4 space-y-2">
  <h3 class="text-sm font-semibold"><?= h($title) ?></h3>
  <?php if (!$items): ?><p class="text-xs text-slate-500">No data yet.</p><?php endif; ?>
  <?php foreach ($items as $i => $item): ?>
  <div class="rounded-xl p-2 flex justify-between <?= $i===0?'bg-amber-50':'bg-slate-50' ?>">
    <span class="text-sm"><?= $medals[$i] ?? '•' ?> <?= h($item['name']) ?></span>
    <span class="text-xs"><?= (int)$item['points'] ?> pt · ✨ <?= (int)$item['salawat'] ?></span>
  </div>
  <?php endforeach; ?>
</section>
<?php endforeach; ?>

<footer class="text-center text-xs text-slate-500 py-4">© 2026 SKJM Vengara</footer>
