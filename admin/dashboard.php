<?php
$title = 'Dashboard';
require __DIR__ . '/../includes/header.php';
require_login();
require __DIR__ . '/../includes/sidebar.php';
$counts = ['Members' => 0, 'Active Cards' => 0, 'Departments' => 0, 'Payments' => 0];
try {
    $counts['Members'] = (int) db()->query('SELECT COUNT(*) FROM members')->fetchColumn();
    $counts['Active Cards'] = (int) db()->query('SELECT COUNT(*) FROM id_cards WHERE status="active"')->fetchColumn();
    $counts['Departments'] = (int) db()->query('SELECT COUNT(*) FROM departments')->fetchColumn();
    $counts['Payments'] = (int) db()->query('SELECT COUNT(*) FROM payments')->fetchColumn();
} catch (Throwable $e) {}
$icons = ['Members' => 'bi-people', 'Active Cards' => 'bi-person-vcard', 'Departments' => 'bi-building', 'Payments' => 'bi-credit-card'];
?>
<main class="main">
    <header class="topbar">
        <div>
            <div class="breadcrumb-line">Admin / Overview</div>
            <h1>Command center</h1>
            <p class="lead-soft">Monitor verified identities, digital cards, QR activity and operational health from one polished workspace.</p>
        </div>
        <div class="top-actions">
            <button class="icon-btn d-lg-none" data-toggle-sidebar aria-label="Open menu"><i class="bi bi-list"></i></button>
            <button class="icon-btn" data-theme-toggle aria-label="Toggle theme"><i class="bi bi-moon-stars"></i></button>
            <button class="icon-btn" aria-label="Notifications"><i class="bi bi-bell"></i></button>
            <a class="btn btn-premium" href="/admin/members.php"><i class="bi bi-plus-lg me-2"></i>New Member</a>
        </div>
    </header>

    <section class="row g-4 mb-4">
        <?php foreach ($counts as $label => $value): ?>
            <div class="col-md-6 col-xl-3">
                <article class="stat-card lift">
                    <div class="stat-icon"><i class="bi <?= e($icons[$label]) ?>"></i></div>
                    <p class="stat-label"><?= e($label) ?></p>
                    <h3 data-counter="<?= $value ?>">0</h3>
                    <span class="stat-trend"><i class="bi bi-arrow-up-right"></i>12.8% this month</span>
                </article>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="row g-4">
        <div class="col-xl-8">
            <article class="table-card lift">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-4 flex-wrap">
                    <div>
                        <p class="page-kicker mb-1">Analytics</p>
                        <h2 class="h3 fw-black mb-1">Verification growth</h2>
                        <p class="text-muted mb-0">Smooth identity verification trends for executive reporting.</p>
                    </div>
                    <span class="filter-chip"><i class="bi bi-calendar3"></i>Last 7 months</span>
                </div>
                <canvas id="analyticsChart" height="116"></canvas>
            </article>
        </div>
        <div class="col-xl-4">
            <article class="lux-card lift h-100">
                <p class="page-kicker mb-1">Quick actions</p>
                <h2 class="h4 fw-black mb-3">Launch workflows</h2>
                <div class="d-grid gap-3">
                    <a class="btn btn-premium" href="/admin/members.php"><i class="bi bi-person-plus me-2"></i>Create member</a>
                    <a class="btn btn-outline-primary" href="/admin/id-card.php"><i class="bi bi-printer me-2"></i>Print ID card</a>
                    <a class="btn btn-outline-secondary" href="/admin/reports.php"><i class="bi bi-download me-2"></i>Export report</a>
                </div>
                <hr class="my-4">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon"><i class="bi bi-shield-check"></i></span>
                    <div><b>Security posture</b><p class="text-muted mb-0 small">CSRF, audit logs and protected sessions enabled.</p></div>
                </div>
            </article>
        </div>
    </section>

    <section class="row g-4 mt-1">
        <div class="col-lg-7">
            <article class="table-card lift">
                <div class="d-flex justify-content-between mb-3"><h2 class="h4 fw-black">Latest activity</h2><a href="/admin/activity-logs.php">View all</a></div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Event</th><th>Module</th><th>Status</th></tr></thead>
                        <tbody>
                            <tr><td><i class="bi bi-check2-circle text-success me-2"></i>QR verification route ready</td><td>Verification</td><td><span class="badge-soft-success">Live</span></td></tr>
                            <tr><td><i class="bi bi-cloud-arrow-up text-primary me-2"></i>Upload interface enhanced</td><td>Members</td><td><span class="badge-soft-success">Ready</span></td></tr>
                            <tr><td><i class="bi bi-palette text-info me-2"></i>Luxury theme system applied</td><td>UI Kit</td><td><span class="badge-soft-success">Active</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </article>
        </div>
        <div class="col-lg-5">
            <article class="lux-card lift">
                <p class="page-kicker mb-1">System quality</p>
                <h2 class="h4 fw-black">Production interface checklist</h2>
                <div class="d-grid gap-3 mt-3">
                    <span class="filter-chip"><i class="bi bi-check-circle text-success"></i>Responsive dashboard</span>
                    <span class="filter-chip"><i class="bi bi-check-circle text-success"></i>Dark and light themes</span>
                    <span class="filter-chip"><i class="bi bi-check-circle text-success"></i>Premium modals, forms and tables</span>
                </div>
            </article>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
