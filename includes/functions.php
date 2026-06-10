<?php
const CARD_FIELDS = [
    'name' => ['label' => 'Name', 'column' => 'name', 'required' => true],
    'class' => ['label' => 'Class', 'column' => 'class', 'required' => false],
    'admission_no' => ['label' => 'Admission Number', 'column' => 'admission_no', 'required' => false],
    'register_num' => ['label' => 'Register Number', 'column' => 'register_num', 'required' => false],
    'chest_no' => ['label' => 'Chest Number', 'column' => 'chest_no', 'required' => false],
    'section' => ['label' => 'Section', 'column' => 'section', 'required' => false],
    'house_name' => ['label' => 'House Name', 'column' => 'house_name', 'required' => false],
    'phone' => ['label' => 'Phone', 'column' => 'phone', 'required' => false],
    'item_type' => ['label' => 'Item Name', 'column' => 'item_type', 'required' => false],
];

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        die('Invalid CSRF token. Please go back and try again.');
    }
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'][] = ['message' => $message, 'type' => $type];
}

function render_flash(): void
{
    foreach ($_SESSION['flash'] ?? [] as $item) {
        echo '<div class="alert alert-' . e($item['type']) . ' alert-dismissible fade show" role="alert">' . e($item['message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    unset($_SESSION['flash']);
}

function post_value(string $key, ?array $source = null): string
{
    if (isset($_POST[$key])) {
        return trim((string)$_POST[$key]);
    }
    return trim((string)($source[$key] ?? ''));
}

function get_templates(mysqli $conn): array
{
    $templates = [];
    $result = $conn->query('SELECT * FROM card_templates ORDER BY is_default DESC, template_name ASC');
    while ($result && $row = $result->fetch_assoc()) {
        $templates[] = $row;
    }
    return $templates;
}

function get_template(mysqli $conn, int $id = 0): ?array
{
    if ($id > 0) {
        $stmt = $conn->prepare('SELECT * FROM card_templates WHERE id = ?');
        $stmt->bind_param('i', $id);
    } else {
        $stmt = $conn->prepare('SELECT * FROM card_templates ORDER BY is_default DESC, id ASC LIMIT 1');
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $template = $result->fetch_assoc() ?: null;
    $stmt->close();
    return $template;
}

function parse_field_config(?string $json): array
{
    $config = json_decode((string)$json, true);
    if (!is_array($config)) {
        $config = [];
    }
    foreach (CARD_FIELDS as $key => $field) {
        if ($field['required']) {
            $config[$key] = 1;
        } elseif (!array_key_exists($key, $config)) {
            $config[$key] = 0;
        }
    }
    return $config;
}

function selected_student_ids(mysqli $conn): array
{
    $mode = $_REQUEST['selection_mode'] ?? 'selected';
    $ids = [];
    if ($mode === 'all') {
        $result = $conn->query('SELECT id FROM users ORDER BY name ASC');
        while ($result && $row = $result->fetch_assoc()) {
            $ids[] = (int)$row['id'];
        }
        return $ids;
    }
    if ($mode === 'class') {
        $class = trim((string)($_REQUEST['bulk_class'] ?? ''));
        if ($class !== '') {
            $stmt = $conn->prepare('SELECT id FROM users WHERE class = ? ORDER BY name ASC');
            $stmt->bind_param('s', $class);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $ids[] = (int)$row['id'];
            }
            $stmt->close();
        }
        return $ids;
    }
    $rawIds = $_REQUEST['student_ids'] ?? [];
    if (!is_array($rawIds)) {
        $rawIds = [$rawIds];
    }
    foreach ($rawIds as $id) {
        foreach (preg_split('/[^0-9]+/', (string)$id, -1, PREG_SPLIT_NO_EMPTY) as $piece) {
            $id = (int)$piece;
            if ($id > 0) {
                $ids[] = $id;
            }
        }
    }
    return array_values(array_unique($ids));
}

function fetch_students_by_ids(mysqli $conn, array $ids): array
{
    if (!$ids) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $conn->prepare("SELECT * FROM users WHERE id IN ($placeholders) ORDER BY class ASC, section ASC, name ASC");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
    return $students;
}

function log_card_action(mysqli $conn, string $action, string $details = ''): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $conn->prepare('INSERT INTO card_logs (action, details, ip_address) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $action, $details, $ip);
    $stmt->execute();
    $stmt->close();
}

function record_export(mysqli $conn, string $type, ?int $templateId, int $count, string $fileName = ''): void
{
    $stmt = $conn->prepare('INSERT INTO card_exports (export_type, template_id, student_count, file_name) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('siis', $type, $templateId, $count, $fileName);
    $stmt->execute();
    $stmt->close();
}

function render_card_html(array $template, array $student, bool $standalone = false): string
{
    $fields = parse_field_config($template['field_config'] ?? '');
    $rows = '';
    foreach (CARD_FIELDS as $key => $meta) {
        if (empty($fields[$key])) {
            continue;
        }
        $value = trim((string)($student[$meta['column']] ?? ''));
        if ($value === '') {
            continue;
        }
        $rows .= '<tr><th>' . e(strtoupper($meta['label'])) . ':</th><td>' . e($value) . '</td></tr>';
    }
    $footer = trim((string)($template['footer_text'] ?? ''));
    $card = '<div class="student-card printable-card">'
        . '<div class="card-heading"><h2>' . e($template['main_heading'] ?? '') . '</h2><h3>' . e($template['sub_heading'] ?? '') . '</h3></div>'
        . '<table class="card-info-table"><tbody>' . $rows . '</tbody></table>'
        . ($footer !== '' ? '<div class="card-footer-text">' . e($footer) . '</div>' : '')
        . '</div>';
    return $standalone ? '<div class="card-page-item">' . $card . '</div>' : $card;
}

function page_title(): string
{
    return 'Student Card Admin';
}
