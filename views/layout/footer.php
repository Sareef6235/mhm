</main>
<nav class="fixed bottom-0 left-0 right-0 bg-white/95 border-t border-slate-200">
  <?php $p = $_GET['page'] ?? 'home'; ?>
  <div class="max-w-[480px] mx-auto grid grid-cols-4 gap-1 p-2 text-xs">
    <a href="<?= app_url('index.php?page=home') ?>" class="rounded-xl text-center p-2 <?= $p==='home'?'bg-emerald-50 text-emerald-700':'text-slate-500' ?>">Home</a>
    <a href="<?= app_url('index.php?page=tracker') ?>" class="rounded-xl text-center p-2 <?= in_array($p,['tracker','record'], true)?'bg-emerald-50 text-emerald-700':'text-slate-500' ?>">Tracker</a>
    <a href="<?= app_url('index.php?page=history') ?>" class="rounded-xl text-center p-2 <?= $p==='history'?'bg-emerald-50 text-emerald-700':'text-slate-500' ?>">History</a>
    <a href="<?= app_url('index.php?page=profile') ?>" class="rounded-xl text-center p-2 <?= $p==='profile'?'bg-emerald-50 text-emerald-700':'text-slate-500' ?>">Profile</a>
  </div>
</nav>
<script>lucide.createIcons();</script>
</body>
</html>
