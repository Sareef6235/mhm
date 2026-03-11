<header class="bg-white rounded-2xl shadow-sm p-4"><h2 class="text-base font-bold">📜 ഹിസ്റ്ററി</h2></header>

<form method="GET" class="bg-white rounded-2xl shadow-sm p-4 space-y-2 text-sm">
  <input type="hidden" name="page" value="history">
  <select name="class_id" class="w-full border rounded-xl p-2">
    <option value="">Class filter</option>
    <?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= ((int)($filters['class_id'] ?? 0) === (int)$c['id'])?'selected':'' ?>><?= h($c['class_name']) ?></option><?php endforeach; ?>
  </select>
  <input type="date" name="date" value="<?= h((string)($filters['date'] ?? '')) ?>" class="w-full border rounded-xl p-2">
  <select name="student_id" class="w-full border rounded-xl p-2">
    <option value="">Student filter</option>
    <?php foreach ($students as $s): ?><option value="<?= $s['id'] ?>" <?= ((int)($filters['student_id'] ?? 0) === (int)$s['id'])?'selected':'' ?>><?= h($s['name']) ?></option><?php endforeach; ?>
  </select>
  <div class="flex gap-2">
    <button class="flex-1 rounded-xl bg-emerald-600 text-white p-2">Filter</button>
    <a href="index.php?page=history&export=csv" class="flex-1 text-center rounded-xl bg-amber-500 text-white p-2">Export CSV</a>
  </div>
</form>

<?php foreach ($rows as $r): ?>
<article class="bg-white rounded-2xl shadow-sm p-4 space-y-2 text-sm">
  <div class="flex justify-between"><div><p class="font-semibold"><?= h($r['student_name']) ?></p><p class="text-xs text-slate-500"><?= h($r['date']) ?></p></div><div class="text-right text-xs"><p><?= (int)$r['total_points'] ?> pt</p><p>✨ <?= (int)$r['salawat'] ?></p></div></div>
  <div class="flex gap-2 text-xs overflow-x-auto">
    <?php
    $colors = ['1' => 'bg-emerald-100 text-emerald-700', '0' => 'bg-slate-200 text-slate-600'];
    foreach (['subah'=>'Subah','dhuhr'=>'Dhuhr','asr'=>'Asr','maghrib'=>'Maghrib','isha'=>'Isha'] as $k=>$label):
      $value = (string)$r[$k];
      $cls = $colors[$value] ?? 'bg-amber-100 text-amber-700';
    ?>
      <span class="px-2 py-1 rounded-full <?= $cls ?>"><?= $label ?></span>
    <?php endforeach; ?>
  </div>
</article>
<?php endforeach; ?>
<?php if (!$rows): ?><p class="text-xs text-slate-500">റെക്കോർഡുകൾ ലഭ്യമല്ല.</p><?php endif; ?>
