<?php $appConfig = require __DIR__ . '/../../config.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= h(($title ?? 'Home') . ' · ' . ($appConfig['app']['name'] ?? 'SKJM Prayer Tracker')) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-100 text-slate-800">
<main class="max-w-[480px] mx-auto min-h-screen pb-24 px-4 pt-4 space-y-4">
