<?php
$title = 'Categories';
require __DIR__ . '/../includes/header.php';
require_login();
require __DIR__ . '/../includes/sidebar.php';
?>
<main class="main">
    <header class="topbar">
        <div>
            <div class="breadcrumb-line">Admin / Categories</div>
            <h1>Categories</h1>
            <p class="lead-soft">A polished premium workspace with responsive controls, beautiful cards, filters, exports and modern interactions.</p>
        </div>
        <div class="top-actions">
            <button class="icon-btn d-lg-none" data-toggle-sidebar aria-label="Open menu"><i class="bi bi-list"></i></button>
            <button class="icon-btn" data-theme-toggle aria-label="Toggle theme"><i class="bi bi-moon-stars"></i></button>
            <button class="btn btn-premium"><i class="bi bi-plus-lg me-2"></i>Create</button>
        </div>
    </header>
    <section class="row g-4 mb-4">
        <div class="col-md-4"><article class="stat-card lift"><div class="stat-icon"><i class="bi bi-stars"></i></div><p class="stat-label">Total records</p><h3 data-counter="128">0</h3><span class="stat-trend"><i class="bi bi-arrow-up-right"></i>Healthy</span></article></div>
        <div class="col-md-4"><article class="stat-card lift"><div class="stat-icon"><i class="bi bi-shield-check"></i></div><p class="stat-label">Verified</p><h3 data-counter="96">0</h3><span class="stat-trend"><i class="bi bi-check2"></i>Secure</span></article></div>
        <div class="col-md-4"><article class="stat-card lift"><div class="stat-icon"><i class="bi bi-lightning-charge"></i></div><p class="stat-label">Automation</p><h3 data-counter="24">0</h3><span class="stat-trend"><i class="bi bi-cpu"></i>Active</span></article></div>
    </section>
    <section class="table-card lift">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
            <div><p class="page-kicker mb-1">Management</p><h2 class="h3 fw-black mb-0">Categories control center</h2></div>
            <div class="d-flex gap-2 flex-wrap"><span class="filter-chip"><i class="bi bi-funnel"></i>Filter</span><span class="filter-chip"><i class="bi bi-download"></i>Export</span><span class="filter-chip"><i class="bi bi-printer"></i>Print</span></div>
        </div>
        <div class="input-icon mb-4"><i class="bi bi-search"></i><input class="form-control search-input" placeholder="Search Categories records"></div>
        <div class="table-responsive">
            <table class="table align-middle"><thead><tr><th>Name</th><th>Status</th><th>Updated</th><th class="text-end">Action</th></tr></thead><tbody><tr><td><b>Premium Categories Item</b><div class="text-muted small">Configured from admin settings</div></td><td><span class="badge-soft-success">Active</span></td><td>Today</td><td class="text-end"><button class="btn btn-sm btn-outline-primary">Manage</button></td></tr></tbody></table>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
