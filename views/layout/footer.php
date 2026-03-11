</main>
<nav class="fixed bottom-0 left-0 right-0 bg-white/95 border-t border-slate-200">
  <div class="max-w-[480px] mx-auto grid grid-cols-4 gap-1 p-2 text-xs">
    <?php $p = $_GET['page'] ?? 'home'; ?>
    <a href="index.php?page=home" class="rounded-xl text-center p-2 <?= $p==='home'?'bg-emerald-50 text-emerald-700':'text-slate-500' ?>">🏠<br>ഹോം</a>
    <a href="index.php?page=tracker" class="rounded-xl text-center p-2 <?= $p==='tracker' || $p==='record'?'bg-emerald-50 text-emerald-700':'text-slate-500' ?>">📿<br>ട്രാക്കർ</a>
    <a href="index.php?page=history" class="rounded-xl text-center p-2 <?= $p==='history'?'bg-emerald-50 text-emerald-700':'text-slate-500' ?>">📜<br>ഹിസ്റ്ററി</a>
    <a href="index.php?page=profile" class="rounded-xl text-center p-2 <?= $p==='profile'?'bg-emerald-50 text-emerald-700':'text-slate-500' ?>">👤<br>പ്രൊഫൈൽ</a>
  </div>
</nav>
<script>lucide.createIcons();</script>
</body></html>
