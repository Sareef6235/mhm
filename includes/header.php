<?php
require_once __DIR__ . '/functions.php';
secure_session();
$appName = setting('website_name', 'SmartID Pro');
$pageTitle = $title ?? $appName;
?>
<!doctype html>
<html lang="en" data-bs-theme="<?= e(setting('theme', 'light')) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e(setting('meta_description', 'Smart Digital ID Card Management System')) ?>">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div id="loader" class="loader" aria-label="Loading SmartID Pro">
    <div class="loader-card">
        <span class="loader-orb"></span>
        <div>
            <strong><?= e($appName) ?></strong>
            <small>Preparing premium workspace</small>
        </div>
    </div>
</div>
