<header class="bg-white rounded-2xl shadow-sm p-4">
  <h2 class="font-bold text-base">Prayer Recording</h2>
  <p class="text-sm"><?= h($student['name']) ?></p>
</header>

<?php if ($message): ?><div class="bg-emerald-100 text-emerald-800 rounded-xl p-3 text-sm"><?= h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="bg-red-100 text-red-700 rounded-xl p-3 text-sm"><?= h($error) ?></div><?php endif; ?>

<form method="POST" class="bg-white rounded-2xl shadow-sm p-4 space-y-3 text-sm">
  <label class="block">Date <input type="date" name="date" value="<?= date('Y-m-d') ?>" class="w-full border rounded-xl p-2" required></label>
  <?php foreach (['subah' => 'Subah', 'dhuhr' => 'Dhuhr', 'asr' => 'Asr', 'maghrib' => 'Maghrib', 'isha' => 'Isha'] as $key => $label): ?>
    <label class="flex justify-between bg-slate-50 rounded-xl p-2"><span><?= $label ?></span><input type="checkbox" name="<?= $key ?>" value="1"></label>
  <?php endforeach; ?>
  <label class="block">Salawat <input type="number" name="salawat" min="0" value="0" class="w-full border rounded-xl p-2"></label>
  <button class="w-full rounded-xl bg-emerald-600 text-white p-2">Save Record</button>
</form>
