<?php
$title = 'Verify ID';
require __DIR__ . '/../includes/header.php';
$token = basename($_SERVER['REQUEST_URI']);
$stmt = db()->prepare('SELECT m.*,d.name department FROM members m LEFT JOIN departments d ON d.id=m.department_id WHERE m.verify_token=? AND m.status="active" LIMIT 1');
$stmt->execute([$token]);
$m = $stmt->fetch();
?>
<main class="auth-page">
    <section class="glass-card auth-card text-center lift">
        <?php if ($m): ?>
            <span class="badge-verified"><i class="bi bi-patch-check"></i>Online Verification Badge</span>
            <img loading="lazy" class="rounded-circle my-4" width="138" height="138" style="object-fit:cover;box-shadow:var(--shadow-md)" src="/<?= e($m['photo'] ?: 'assets/images/avatar.svg') ?>" alt="Verified member photo">
            <h1 class="h2 fw-black"><?= e($m['full_name']) ?></h1>
            <p class="text-muted"><?= e($m['member_uid']) ?> • <?= e($m['department']) ?></p>
            <div class="list-group text-start rounded-4 overflow-hidden">
                <div class="list-group-item py-3">Status: <b><?= e($m['status']) ?></b></div>
                <div class="list-group-item py-3">Joining Date: <b><?= e($m['joining_date']) ?></b></div>
                <div class="list-group-item py-3">Verification: <b>QR token matched securely</b></div>
            </div>
        <?php else: ?>
            <i class="bi bi-x-octagon fs-1 text-danger"></i><h1 class="h4 text-danger mt-3">ID Not Verified</h1><p class="text-muted">The scanned card is invalid, expired or disabled.</p>
        <?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
