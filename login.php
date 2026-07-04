<?php
$title = 'Login';
require __DIR__ . '/includes/header.php';
if (!empty($_SESSION['user'])) header('Location: /admin/dashboard.php');
?>
<main class="auth-page">
    <section class="glass-card auth-card lift">
        <div class="text-center mb-4">
            <span class="brand-mark mx-auto mb-3">ID</span>
            <p class="page-kicker mb-1">Secure portal</p>
            <h1 class="h2 fw-black">Welcome back</h1>
            <p class="text-muted">Sign in to your premium digital identity command center.</p>
        </div>
        <form method="post" action="/api/login.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="input-icon mb-3"><i class="bi bi-envelope"></i><input name="email" type="email" class="form-control" placeholder="Email address" required></div>
            <div class="input-icon mb-3"><i class="bi bi-lock"></i><input name="password" type="password" class="form-control" placeholder="Password" required></div>
            <button class="btn btn-premium w-100"><i class="bi bi-shield-lock me-2"></i>Login Securely</button>
            <p class="small text-center text-muted mt-3 mb-0">Default: admin@example.com / password</p>
        </form>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
