<header class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white rounded-2xl p-5 shadow-sm">
  <h1 class="text-xl font-bold">🕌 നിസ്കാരം ട്രാക്കർ</h1>
  <p class="text-xs text-emerald-100">Daily Prayer & Study Tracker</p>
</header>

<section class="bg-white rounded-2xl shadow-sm p-4 text-sm">
  <p class="text-xs text-slate-500">ഇന്നത്തെ തിയതി</p>
  <p class="font-semibold"><?= date('F d, l') ?></p>
</section>

<form class="bg-white rounded-2xl shadow-sm p-4" method="GET">
  <input type="hidden" name="page" value="home">
  <select name="class_id" class="w-full rounded-xl border p-2 text-sm" onchange="this.form.submit()">
    <option value="">എല്ലാ ക്ലാസുകളും</option>
    <?php foreach ($classes as $c): ?>
      <option value="<?= $c['id'] ?>" <?= ($classId === (int)$c['id']) ? 'selected' : '' ?>><?= h($c['class_name']) ?></option>
    <?php endforeach; ?>
  </select>
</form>

<?php
$sections = ['ഇന്നത്തെ ടോപ്പർ 🏆' => $daily, 'ആഴ്ചയിലെ 📅' => $week, 'മാസത്തിലെ 📆' => $month];
$medals = ['🥇', '🥈', '🥉'];
foreach ($sections as $label => $items): ?>
<section class="bg-white rounded-2xl shadow-sm p-4 space-y-2">
  <h2 class="text-sm font-semibold"><?= $label ?></h2>
  <?php if (!$items): ?>
    <p class="text-xs text-slate-500">റെക്കോർഡുകൾ ലഭ്യമല്ല.</p>
  <?php endif; ?>
  <?php foreach ($items as $idx => $item): ?>
    <div class="rounded-xl p-2 <?= $idx === 0 ? 'bg-amber-50' : 'bg-slate-50' ?> flex justify-between text-sm">
      <span><?= $medals[$idx] ?? '•' ?> <?= h($item['name']) ?></span>
      <span class="text-xs"><?= (int)$item['points'] ?> pt · ✨ <?= (int)$item['salawat'] ?></span>
    </div>
  <?php endforeach; ?>
</section>
<?php endforeach; ?>

<footer class="text-center text-xs text-slate-500 py-4">
  <p>© 2026</p>
  <p>Designed by Muhsin Faizy</p>
</footer>
