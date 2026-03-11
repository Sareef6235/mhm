<?php
require_once __DIR__ . '/../core/Database.php';

class PrayerRecordModel {
    public static function saveDaily(array $payload): bool {
        $points = (int)$payload['subah'] + (int)$payload['dhuhr'] + (int)$payload['asr'] + (int)$payload['maghrib'] + (int)$payload['isha'];

        $sql = 'INSERT INTO prayer_records
            (student_id, class_id, date, subah, dhuhr, asr, maghrib, isha, salawat, total_points)
            VALUES (:student_id, :class_id, :date, :subah, :dhuhr, :asr, :maghrib, :isha, :salawat, :total_points)';

        $stmt = Database::connection()->prepare($sql);

        try {
            return $stmt->execute([
                'student_id' => $payload['student_id'],
                'class_id' => $payload['class_id'],
                'date' => $payload['date'],
                'subah' => $payload['subah'],
                'dhuhr' => $payload['dhuhr'],
                'asr' => $payload['asr'],
                'maghrib' => $payload['maghrib'],
                'isha' => $payload['isha'],
                'salawat' => $payload['salawat'],
                'total_points' => $points,
            ]);
        } catch (PDOException $e) {
            if ((int)$e->errorInfo[1] === 1062) {
                return false;
            }
            throw $e;
        }
    }

    public static function history(array $filters = []): array {
        $sql = 'SELECT pr.*, s.name as student_name, c.class_name
            FROM prayer_records pr
            JOIN students s ON s.id = pr.student_id
            JOIN classes c ON c.id = pr.class_id
            WHERE 1=1';
        $params = [];

        if (!empty($filters['class_id'])) {
            $sql .= ' AND pr.class_id = :class_id';
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

        $sql .= ' ORDER BY pr.date DESC, pr.total_points DESC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function leaderboard(string $range): array {
        $dateClause = 'pr.date = CURDATE()';
        if ($range === 'week') {
            $dateClause = 'YEARWEEK(pr.date, 1) = YEARWEEK(CURDATE(), 1)';
        } elseif ($range === 'month') {
            $dateClause = 'MONTH(pr.date) = MONTH(CURDATE()) AND YEAR(pr.date) = YEAR(CURDATE())';
        }

        $sql = "SELECT s.name, SUM(pr.total_points) AS points, SUM(pr.salawat) AS salawat
                FROM prayer_records pr
                JOIN students s ON s.id = pr.student_id
                WHERE {$dateClause}
                GROUP BY pr.student_id
                ORDER BY points DESC, salawat DESC
                LIMIT 3";
        $stmt = Database::connection()->query($sql);
        return $stmt->fetchAll();
    }

    public static function exportCsv(array $filters = []): string {
        $rows = self::history($filters);
        $fh = fopen('php://temp', 'r+');
        fputcsv($fh, ['Student', 'Class', 'Date', 'Points', 'Salawat', 'Subah', 'Dhuhr', 'Asr', 'Maghrib', 'Isha']);
        foreach ($rows as $r) {
            fputcsv($fh, [
                $r['student_name'],
                $r['class_name'],
                $r['date'],
                $r['total_points'],
                $r['salawat'],
                $r['subah'],
                $r['dhuhr'],
                $r['asr'],
                $r['maghrib'],
                $r['isha'],
            ]);
        }
        rewind($fh);
        return stream_get_contents($fh);
    }
}
