<?php require_once __DIR__ . '/functions.php'; secure_session(); ?>
<!doctype html><html lang="en" data-bs-theme="<?= e(setting('theme','light')) ?>"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="description" content="<?= e(setting('meta_description','Smart Digital ID Card Management System')) ?>">
<title><?= e($title ?? setting('website_name','Smart Digital ID')) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"><link href="/assets/css/style.css" rel="stylesheet"></head>
<body><div id="loader" class="loader"><span></span></div>
