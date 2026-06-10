<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$template = get_template($conn, $id);
if (!$template) { flash('Template not found.', 'danger'); redirect('templates.php'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $fieldConfig = [];
    foreach (CARD_FIELDS as $key => $meta) { $fieldConfig[$key] = $meta['required'] ? 1 : (isset($_POST['fields'][$key]) ? 1 : 0); }
    $name = trim($_POST['template_name'] ?? ''); $main = trim($_POST['main_heading'] ?? ''); $sub = trim($_POST['sub_heading'] ?? ''); $footer = trim($_POST['footer_text'] ?? ''); $cpp = max(1, min(4, (int)($_POST['cards_per_page'] ?? 2))); $isDefault = isset($_POST['is_default']) ? 1 : 0;
    if ($name === '' || $main === '') { flash('Template name and main heading are required.', 'danger'); }
    else {
        if ($isDefault) { $conn->query('UPDATE card_templates SET is_default = 0'); }
        $json = json_encode($fieldConfig, JSON_UNESCAPED_UNICODE);
        $stmt = $conn->prepare('UPDATE card_templates SET template_name=?, main_heading=?, sub_heading=?, footer_text=?, field_config=?, cards_per_page=?, is_default=? WHERE id=?');
        $stmt->bind_param('sssssiii', $name, $main, $sub, $footer, $json, $cpp, $isDefault, $id);
        $stmt->execute(); $stmt->close();
        log_card_action($conn, 'template_updated', $name); flash('Template updated successfully.'); redirect('templates.php');
    }
}
render_header('Edit Template');
?>
<h1 class="h3 mb-3">Edit Template</h1><?php include __DIR__ . '/template-form.php'; ?><?php render_footer(); ?>
