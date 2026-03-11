<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/ClassModel.php';

class StudentModel
{
    public static function all(?int $classId = null, ?string $search = null): array
    {
        $sql = 'SELECT s.id, s.name, s.phone, s.dob, s.class_id, c.class_name
                FROM students s
                JOIN classes c ON c.id = s.class_id
                WHERE 1=1';
        $params = [];

        if ($classId) {
            $sql .= ' AND s.class_id = :class_id';
            $params['class_id'] = $classId;
        }
        if ($search) {
            $sql .= ' AND (s.name LIKE :search OR s.phone LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY s.name';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT s.*, c.class_name FROM students s JOIN classes c ON c.id = s.class_id WHERE s.id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(string $name, string $phone, string $dob, int $classId, ?string $password = null): void
    {
        $hash = password_hash($password ?: $dob, PASSWORD_DEFAULT);
        $stmt = Database::connection()->prepare('INSERT INTO students (name, phone, dob, password, class_id) VALUES (:name,:phone,:dob,:password,:class_id)');
        $stmt->execute([
            'name' => $name,
            'phone' => $phone,
            'dob' => $dob,
            'password' => $hash,
            'class_id' => $classId,
        ]);
    }

    public static function update(int $id, string $name, string $phone, string $dob, int $classId): void
    {
        $stmt = Database::connection()->prepare('UPDATE students SET name=:name, phone=:phone, dob=:dob, class_id=:class_id WHERE id=:id');
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'phone' => $phone,
            'dob' => $dob,
            'class_id' => $classId,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM students WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function authenticate(string $phone, string $password, string $dob): ?array
    {
        $stmt = Database::connection()->prepare('SELECT s.*, c.class_name FROM students s JOIN classes c ON c.id = s.class_id WHERE s.phone = :phone LIMIT 1');
        $stmt->execute(['phone' => $phone]);
        $student = $stmt->fetch();

        if (!$student) {
            return null;
        }

        if (!password_verify($password, $student['password'])) {
            return null;
        }

        if ($student['dob'] !== $dob) {
            return null;
        }

        return $student;
    }

    public static function bulkImportFromCsv(string $tmpPath): array
    {
        $handle = fopen($tmpPath, 'r');
        if (!$handle) {
            return ['inserted' => 0, 'skipped' => 0, 'errors' => ['Unable to read file']];
        }

        $header = fgetcsv($handle);
        $inserted = 0;
        $skipped = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) {
                $skipped++;
                continue;
            }

            [$name, $phone, $dob, $classValue] = array_map('trim', $row);
            if ($name === '' || $phone === '' || $dob === '' || $classValue === '') {
                $skipped++;
                continue;
            }

            $classId = is_numeric($classValue) ? (int)$classValue : ClassModel::findIdByName('Class ' . preg_replace('/[^0-9]/', '', $classValue));
            if (!$classId) {
                $classId = ClassModel::findIdByName($classValue);
            }
            if (!$classId) {
                $errors[] = "Unknown class for {$name}";
                $skipped++;
                continue;
            }

            try {
                self::create($name, $phone, $dob, $classId, $dob);
                $inserted++;
            } catch (Throwable $e) {
                $skipped++;
            }
        }

        fclose($handle);
        return compact('inserted', 'skipped', 'errors');
    }
}
