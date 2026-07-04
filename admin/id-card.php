<?php
$title = 'ID Card';
require __DIR__ . '/../includes/header.php';
require_login();
require __DIR__ . '/../includes/sidebar.php';
$stmt = db()->prepare('SELECT * FROM members WHERE id=?');
$stmt->execute([$_GET['id'] ?? 0]);
$m = $stmt->fetch() ?: [];
?>
<main class="main">
    <header class="topbar">
        <div><div class="breadcrumb-line">Admin / Digital ID Cards</div><h1>Digital card studio</h1><p class="lead-soft">Preview a luxury printable identity card with QR verification, seal-ready layout and premium spacing.</p></div>
        <div class="top-actions"><button class="icon-btn d-lg-none" data-toggle-sidebar><i class="bi bi-list"></i></button><button class="icon-btn" data-theme-toggle><i class="bi bi-moon-stars"></i></button><button onclick="print()" class="btn btn-premium"><i class="bi bi-printer me-2"></i>Print / PDF</button></div>
    </header>
    <section class="row g-4 align-items-start">
        <div class="col-lg-5"><article class="id-card lift"><div class="position-relative text-center"><div class="brand-mark mx-auto mb-3">ID</div><p class="page-kicker mb-1"><?= e(setting('website_name', 'SmartID Pro')) ?></p><h2 class="h4 fw-black">Official Digital Identity</h2><img loading="lazy" class="rounded-circle my-4" width="132" height="132" style="object-fit:cover;box-shadow:var(--shadow-md)" src="/<?= e($m['photo'] ?? 'assets/images/avatar.svg') ?>" alt="Member photo"><h3><?= e($m['full_name'] ?? 'Member Name') ?></h3><p class="text-muted mb-3"><?= e($m['occupation'] ?? 'Verified Member') ?></p><div class="qr-box"><i class="bi bi-qr-code fs-1"></i></div><p class="mt-3 fw-black"><?= e($m['member_uid'] ?? 'MID-0000') ?></p><span class="badge-verified"><i class="bi bi-patch-check"></i>Verified Active</span></div></article></div>
        <div class="col-lg-7"><article class="lux-card lift"><p class="page-kicker mb-1">Back side details</p><h2 class="h3 fw-black">Ready for print, share and verification</h2><p class="text-muted">Supports portrait, landscape, wallet and A4 presentation with logo placement, signature, digital seal, expiry date, QR token and barcode-ready zones.</p><div class="d-flex gap-2 flex-wrap my-4"><span class="filter-chip"><i class="bi bi-aspect-ratio"></i>Responsive layouts</span><span class="filter-chip"><i class="bi bi-qr-code"></i>Secure QR</span><span class="filter-chip"><i class="bi bi-cloud-download"></i>PDF-ready</span></div><a class="btn btn-outline-primary" href="/id/<?= e($m['verify_token'] ?? '') ?>"><i class="bi bi-box-arrow-up-right me-2"></i>Open public verification</a></article></div>
    </section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
