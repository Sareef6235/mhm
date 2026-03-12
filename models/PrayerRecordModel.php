<?php
require_once __DIR__ . '/../core/Database.php';

class PrayerRecordModel
{
    public static function calculatePoints(array $payload): int
    {
        return (int)$payload['subah'] + (int)$payload['dhuhr'] + (int)$payload['asr'] + (int)$payload['maghrib'] + (int)$payload['isha'];
    }

    public static function saveDaily(array $payload): bool
    {
        $points = self::calculatePoints($payload);

        $sql = 'INSERT INTO prayer_records
                (student_id, date, subah, dhuhr, asr, maghrib, isha, salawat, points)
                VALUES (:student_id, :date, :subah, :dhuhr, :asr, :maghrib, :isha, :salawat, :points)';

        try {
            $stmt = Database::connection()->prepare($sql);
            return $stmt->execute([
                'student_id' => $payload['student_id'],
                'date' => $payload['date'],
                'subah' => $payload['subah'],
                'dhuhr' => $payload['dhuhr'],
                'asr' => $payload['asr'],
                'maghrib' => $payload['maghrib'],
                'isha' => $payload['isha'],
                'salawat' => $payload['salawat'],
                'points' => $points,
            ]);
        } catch (PDOException $e) {
            if ((int)($e->errorInfo[1] ?? 0) === 1062) {
                return false;
            }
            throw $e;
        }
    }

    public static function history(array $filters = []): array
    {
        $sql = 'SELECT pr.*, s.name AS student_name, s.class_id, c.class_name
                FROM prayer_records pr
                JOIN students s ON s.id = pr.student_id
                JOIN classes c ON c.id = s.class_id
                WHERE 1=1';
        $params = [];

        if (!empty($filters['class_id'])) {
            $sql .= ' AND s.class_id = :class_id';
            $params['class_id'] = $filters['class_id'];
        }
        if (!empty($filters['date'])) {
            $sql .= ' AND pr.date = :date';
            $params['date'] = $filters['date'];
        }
        if (!empty($filters['student_id'])) {
            $sql .= ' AND pr.student_id = :student_id';
            $params['student_id'] = $filters['student_id'];
        }

        $sql .= ' ORDER BY pr.date DESC, pr.points DESC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function leaderboard(string $range = 'day', ?int $classId = null): array
    {
        $dateClause = 'pr.date = CURDATE()';
        if ($range === 'week') {
            $dateClause = 'YEARWEEK(pr.date, 1) = YEARWEEK(CURDATE(), 1)';
        } elseif ($range === 'month') {
            $dateClause = 'MONTH(pr.date) = MONTH(CURDATE()) AND YEAR(pr.date) = YEAR(CURDATE())';
        }

        $sql = "SELECT s.id, s.name, c.class_name, SUM(pr.points) AS points, SUM(pr.salawat) AS salawat
                FROM prayer_records pr
                JOIN students s ON s.id = pr.student_id
                JOIN classes c ON c.id = s.class_id
                WHERE {$dateClause}";
        $params = [];
        if ($classId) {
            $sql .= ' AND s.class_id = :class_id';
            $params['class_id'] = $classId;
        }
        $sql .= ' GROUP BY pr.student_id ORDER BY points DESC, salawat DESC LIMIT 3';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function todayForStudent(int $studentId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM prayer_records WHERE student_id = :student_id AND date = CURDATE() LIMIT 1');
        $stmt->execute(['student_id' => $studentId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function exportCsv(array $filters = []): string
    {
        $rows = self::history($filters);
        $fh = fopen('php://temp', 'r+');
        fputcsv($fh, ['Student', 'Class', 'Date', 'Points', 'Salawat', 'Subah', 'Dhuhr', 'Asr', 'Maghrib', 'Isha']);
        foreach ($rows as $r) {
            fputcsv($fh, [$r['student_name'], $r['class_name'], $r['date'], $r['points'], $r['salawat'], $r['subah'], $r['dhuhr'], $r['asr'], $r['maghrib'], $r['isha']]);
        }
        rewind($fh);
        return stream_get_contents($fh);
    }

    public static function adminStats(): array
    {
        $db = Database::connection();
        $totalStudents = (int)$db->query('SELECT COUNT(*) FROM students')->fetchColumn();
        $totalClasses = (int)$db->query('SELECT COUNT(*) FROM classes')->fetchColumn();
        $totalRecords = (int)$db->query('SELECT COUNT(*) FROM prayer_records')->fetchColumn();
        $todayPoints = (int)$db->query('SELECT COALESCE(SUM(points), 0) FROM prayer_records WHERE date = CURDATE()')->fetchColumn();

        return [
            'total_students' => $totalStudents,
            'total_classes' => $totalClasses,
            'total_records' => $totalRecords,
            'today_points' => $todayPoints,
        ];
    }

    public static function classPerformance(string $range = 'month'): array
    {
        $dateClause = 'pr.date = CURDATE()';
        if ($range === 'week') {
            $dateClause = 'YEARWEEK(pr.date, 1) = YEARWEEK(CURDATE(), 1)';
        } elseif ($range === 'month') {
            $dateClause = 'MONTH(pr.date) = MONTH(CURDATE()) AND YEAR(pr.date) = YEAR(CURDATE())';
        }

        $sql = "SELECT c.class_name, COALESCE(SUM(pr.points), 0) AS points, COUNT(DISTINCT s.id) AS student_count
                FROM classes c
                LEFT JOIN students s ON s.class_id = c.id
                LEFT JOIN prayer_records pr ON pr.student_id = s.id AND {$dateClause}
                GROUP BY c.id
                ORDER BY points DESC, c.id ASC";

        $stmt = Database::connection()->query($sql);
        return $stmt->fetchAll();
    }
}
