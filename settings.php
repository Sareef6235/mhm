<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach (['institution_name','default_cards_per_page','print_page_size','admin_title'] as $key) {
        $value = trim((string)($_POST[$key] ?? ''));
        $stmt = $conn->prepare('INSERT INTO card_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        $stmt->bind_param('ss', $key, $value); $stmt->execute(); $stmt->close();
    }
    log_card_action($conn, 'settings_updated', 'Card settings'); flash('Settings saved.'); redirect('settings.php');
}
$settings = [];
$result = $conn->query('SELECT setting_key, setting_value FROM card_settings');
while ($result && $row = $result->fetch_assoc()) { $settings[$row['setting_key']] = $row['setting_value']; }
render_header('Settings');
?>
<h1 class="h3 mb-3">Settings</h1><form method="post" class="card card-body"><?= csrf_field() ?><div class="row g-3"><div class="col-md-6"><label class="form-label">Institution Name</label><input class="form-control" name="institution_name" value="<?= e($settings['institution_name'] ?? '') ?>"></div><div class="col-md-3"><label class="form-label">Default Cards Per Page</label><input class="form-control" name="default_cards_per_page" value="<?= e($settings['default_cards_per_page'] ?? '2') ?>"></div><div class="col-md-3"><label class="form-label">Print Page Size</label><input class="form-control" name="print_page_size" value="<?= e($settings['print_page_size'] ?? 'A4') ?>"></div><div class="col-md-6"><label class="form-label">Admin Title</label><input class="form-control" name="admin_title" value="<?= e($settings['admin_title'] ?? '') ?>"></div></div><div class="mt-4"><button class="btn btn-dark">Save Settings</button></div></form>
<?php render_footer(); ?>
