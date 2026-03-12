<?php $appConfig = require __DIR__ . '/../../config.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h(($appConfig['app']['name'] ?? 'SKJM Prayer Tracker') . ' - Student Login') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen p-4 flex items-center justify-center">
  <form method="POST" class="w-full max-w-[480px] bg-white rounded-2xl shadow-sm p-5 space-y-3">
    <h1 class="text-lg font-bold"><?= h($appConfig['app']['name'] ?? 'SKJM Prayer Tracker') ?></h1>
    <p class="text-xs text-slate-500">Student Login (Phone + Password + DOB verification)</p>
    <?php if (!empty($error)): ?><p class="text-sm text-red-600"><?= h($error) ?></p><?php endif; ?>
    <input name="phone" inputmode="numeric" pattern="[0-9]{10,15}" required class="w-full border rounded-xl p-2 text-sm" placeholder="Phone Number">
    <input name="password" type="password" required class="w-full border rounded-xl p-2 text-sm" placeholder="Password">
    <label class="text-xs text-slate-600">Date of Birth</label>
    <input name="dob" type="date" required class="w-full border rounded-xl p-2 text-sm">
    <button class="w-full rounded-xl bg-emerald-600 text-white p-2 text-sm">Login</button>
    <p class="text-xs text-slate-500"><?= h($appConfig['app']['footer'] ?? '© 2026 SKJM Vengara') ?></p>
  </form>
</body>
</html>
