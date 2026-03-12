<section class="bg-white rounded-2xl shadow-sm p-5 text-center">
  <h2 class="text-base font-semibold">👤 പ്രൊഫൈൽ</h2>
  <?php if ($student): ?>
    <p class="text-sm mt-2"><?= h($student['name']) ?></p>
    <p class="text-xs text-slate-500"><?= h($student['class_name']) ?> · <?= h($student['phone']) ?></p>
  <?php else: ?>
    <p class="text-sm mt-2 text-slate-500">No student found.</p>
  <?php endif; ?>
</section>
