<header class="bg-white rounded-2xl shadow-sm p-4"><h2 class="text-base font-bold">📜 ഹിസ്റ്ററി</h2></header>

<form method="GET" class="bg-white rounded-2xl shadow-sm p-4 space-y-2 text-sm">
  <input type="hidden" name="page" value="history">
  <select name="class_id" class="w-full border rounded-xl p-2">
    <option value="">Class</option>
    <?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= ((int)($filters['class_id'] ?? 0)===(int)$c['id'])?'selected':'' ?>><?= h($c['class_name']) ?></option><?php endforeach; ?>
  </select>
  <input type="date" name="date" value="<?= h((string)($filters['date'] ?? '')) ?>" class="w-full border rounded-xl p-2">
  <select name="student_id" class="w-full border rounded-xl p-2">
    <option value="">Student</option>
    <?php foreach ($students as $s): ?><option value="<?= $s['id'] ?>" <?= ((int)($filters['student_id'] ?? 0)===(int)$s['id'])?'selected':'' ?>><?= h($s['name']) ?></option><?php endforeach; ?>
  </select>
  <div class="flex gap-2">
    <button class="flex-1 rounded-xl bg-emerald-600 text-white p-2">Apply</button>
    <a href="<?= app_url('index.php?page=history&export=csv') ?>" class="flex-1 rounded-xl bg-amber-500 text-white p-2 text-center">Export CSV</a>
  </div>
</form>

<?php foreach ($rows as $r): ?>
<?php $statusClass = ((int)$r['total_points']===5) ? 'border-emerald-300' : (((int)$r['total_points']>0) ? 'border-yellow-300' : 'border-slate-300'); ?>
<article class="bg-white border <?= $statusClass ?> rounded-2xl shadow-sm p-4 space-y-2 text-sm">
  <div class="flex justify-between">
    <div>
      <p class="font-semibold"><?= h($r['student_name']) ?></p>
      <p class="text-xs text-slate-500"><?= h($r['class_name']) ?> · <?= h($r['date']) ?></p>
    </div>
    <div class="text-right text-xs">
      <p><?= (int)$r['total_points'] ?> pt</p>
      <p>✨ <?= (int)$r['salawat'] ?></p>
    </div>
  </div>
  <div class="grid grid-cols-5 gap-1 text-[10px]">
    <?php foreach (['subah'=>'Subah','dhuhr'=>'Dhuhr','asr'=>'Asr','maghrib'=>'Maghrib','isha'=>'Isha'] as $k=>$label): ?>
      <span class="px-1 py-1 text-center rounded <?= ((int)$r[$k]===1)?'bg-emerald-100 text-emerald-700':'bg-slate-200 text-slate-600' ?>"><?= $label ?></span>
    <?php endforeach; ?>
  </div>
</article>
<?php endforeach; ?>
<?php if (!$rows): ?><p class="text-xs text-slate-500">No records available.</p><?php endif; ?>
