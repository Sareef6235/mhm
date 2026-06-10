<?php
function render_header(string $title): void
{
    $nav = [
        'dashboard.php' => 'Dashboard',
        'students.php' => 'Students',
        'templates.php' => 'Templates',
        'generate-cards.php' => 'Generate Cards',
        'export-pdf.php' => 'PDF Export',
        'export-excel.php' => 'Excel Export',
        'settings.php' => 'Settings',
    ];
    $current = basename($_SERVER['SCRIPT_NAME'] ?? '');
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . e($title) . ' - ' . e(page_title()) . '</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="assets/css/style.css" rel="stylesheet"></head><body>';
    echo '<nav class="navbar navbar-expand-lg navbar-dark bg-dark"><div class="container-fluid"><a class="navbar-brand fw-bold" href="dashboard.php">Card Admin</a><button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"><span class="navbar-toggler-icon"></span></button><div class="collapse navbar-collapse" id="mainNav"><ul class="navbar-nav ms-auto">';
    foreach ($nav as $href => $label) {
        $active = $current === $href ? ' active' : '';
        echo '<li class="nav-item"><a class="nav-link' . $active . '" href="' . e($href) . '">' . e($label) . '</a></li>';
    }
    echo '</ul></div></div></nav><main class="container-fluid py-4"><div class="content-shell">';
    render_flash();
}

function render_footer(): void
{
    echo '</div></main><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><script src="assets/js/app.js"></script></body></html>';
}
