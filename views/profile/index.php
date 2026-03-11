<section class="bg-white rounded-2xl shadow-sm p-5 text-center">
  <h2 class="text-base font-semibold">Profile</h2>
  <p class="text-sm mt-2"><?= h($student['name']) ?></p>
  <p class="text-xs text-slate-500"><?= h($student['class_name']) ?> · <?= h($student['phone']) ?></p>
  <a href="<?= app_url('index.php?page=logout') ?>" class="mt-3 inline-block rounded-xl bg-red-500 text-white px-4 py-2 text-xs">Logout</a>
</section>
<footer class="text-center text-xs text-slate-500 py-4">© 2026 SKJM Vengara</footer>
