<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

function jsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function parseJsonBody(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function input(string $key, mixed $default = ''): mixed
{
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }

    $json = parseJsonBody();
    return $json[$key] ?? $default;
}

$action = $_GET['api'] ?? '';

try {
    switch ($action) {
        case 'list': {
            $month = trim((string)($_GET['month'] ?? ''));
            $search = trim((string)($_GET['search'] ?? ''));

            $sql = 'SELECT id, month_name, ustad_name, week, total_period, subject, lesson_name, lesson_details, activities, smart_date, exam_date, sort_order
                    FROM monthly_plan
                    WHERE 1=1';
            $params = [];

            if ($month !== '') {
                $sql .= ' AND month_name = ?';
                $params[] = $month;
            }

            if ($search !== '') {
                $sql .= ' AND (
                    month_name LIKE ? OR ustad_name LIKE ? OR week LIKE ? OR total_period LIKE ? OR subject LIKE ? OR lesson_name LIKE ? OR lesson_details LIKE ? OR activities LIKE ? OR smart_date LIKE ? OR exam_date LIKE ?
                )';
                $needle = "%{$search}%";
                for ($i = 0; $i < 10; $i++) {
                    $params[] = $needle;
                }
            }

            $sql .= ' ORDER BY sort_order ASC, id DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            jsonResponse(['ok' => true, 'data' => $stmt->fetchAll()]);
        }

        case 'ustads': {
            $stmt = $pdo->query('SELECT id, name FROM ustads ORDER BY name ASC');
            jsonResponse(['ok' => true, 'data' => $stmt->fetchAll()]);
        }

        case 'subjects': {
            $stmt = $pdo->query('SELECT id, name FROM subjects ORDER BY name ASC');
            jsonResponse(['ok' => true, 'data' => $stmt->fetchAll()]);
        }

        case 'save': {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                jsonResponse(['ok' => false, 'message' => 'Method not allowed'], 405);
            }

            $id = (int) input('id', 0);

            $data = [
                'month' => trim((string) input('month')),
                'ustad' => trim((string) input('ustad')),
                'week' => trim((string) input('week')),
                'period' => trim((string) input('period')),
                'subject' => trim((string) input('subject')),
                'lesson' => trim((string) input('lesson')),
                'details' => trim((string) input('details')),
                'activity' => trim((string) input('activity')),
                'smart' => trim((string) input('smart')),
                'exam' => trim((string) input('exam')),
            ];

            if ($id > 0 && $data['subject'] !== '' && $data['month'] === '') {
                $stmt = $pdo->prepare('UPDATE monthly_plan SET subject = ? WHERE id = ?');
                $stmt->execute([$data['subject'], $id]);
                jsonResponse(['ok' => true, 'message' => 'Subject updated']);
            }

            foreach (['month', 'ustad', 'week', 'period', 'subject', 'lesson'] as $required) {
                if ($data[$required] === '') {
                    jsonResponse(['ok' => false, 'message' => "Missing required field: {$required}"], 422);
                }
            }

            if ($id > 0) {
                $stmt = $pdo->prepare(
                    'UPDATE monthly_plan
                     SET month_name = ?, ustad_name = ?, week = ?, total_period = ?, subject = ?, lesson_name = ?, lesson_details = ?, activities = ?, smart_date = ?, exam_date = ?
                     WHERE id = ?'
                );
                $stmt->execute([
                    $data['month'],
                    $data['ustad'],
                    $data['week'],
                    $data['period'],
                    $data['subject'],
                    $data['lesson'],
                    $data['details'],
                    $data['activity'],
                    $data['smart'] ?: null,
                    $data['exam'] ?: null,
                    $id,
                ]);

                jsonResponse(['ok' => true, 'message' => 'Plan updated']);
            }

            $nextOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 1 AS next_order FROM monthly_plan')->fetch()['next_order'];

            $stmt = $pdo->prepare(
                'INSERT INTO monthly_plan (month_name, ustad_name, week, total_period, subject, lesson_name, lesson_details, activities, smart_date, exam_date, sort_order)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $data['month'],
                $data['ustad'],
                $data['week'],
                $data['period'],
                $data['subject'],
                $data['lesson'],
                $data['details'],
                $data['activity'],
                $data['smart'] ?: null,
                $data['exam'] ?: null,
                $nextOrder,
            ]);

            jsonResponse(['ok' => true, 'message' => 'Plan created', 'id' => (int) $pdo->lastInsertId()]);
        }

        case 'delete': {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                jsonResponse(['ok' => false, 'message' => 'Method not allowed'], 405);
            }

            $id = (int) input('id', 0);
            if ($id <= 0) {
                jsonResponse(['ok' => false, 'message' => 'Invalid id'], 422);
            }

            $stmt = $pdo->prepare('DELETE FROM monthly_plan WHERE id = ?');
            $stmt->execute([$id]);

            jsonResponse(['ok' => true, 'message' => 'Plan deleted']);
        }

        case 'reorder': {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                jsonResponse(['ok' => false, 'message' => 'Method not allowed'], 405);
            }

            $rows = input('rows', []);
            if (!is_array($rows)) {
                jsonResponse(['ok' => false, 'message' => 'Invalid payload'], 422);
            }

            $pdo->beginTransaction();
            $stmt = $pdo->prepare('UPDATE monthly_plan SET sort_order = ? WHERE id = ?');
            foreach ($rows as $idx => $id) {
                $stmt->execute([$idx + 1, (int) $id]);
            }
            $pdo->commit();

            jsonResponse(['ok' => true, 'message' => 'Order updated']);
        }

        default:
            jsonResponse(['ok' => false, 'message' => 'Unknown API action'], 404);
    }
} catch (Throwable $e) {
    jsonResponse(['ok' => false, 'message' => 'Server error', 'error' => $e->getMessage()], 500);
}
