<?php
require_once __DIR__ . '/../core/Database.php';

class StudentModel {
    public static function all(?int $classId = null, ?string $search = null): array {
        $sql = 'SELECT s.id, s.name, s.class_id, c.class_name FROM students s JOIN classes c ON c.id = s.class_id WHERE 1=1';
        $params = [];
        if ($classId) {
            $sql .= ' AND s.class_id = :class_id';
            $params['class_id'] = $classId;
        }
        if ($search) {
            $sql .= ' AND s.name LIKE :search';
            $params['search'] = '%' . $search . '%';
        }
        $sql .= ' ORDER BY s.name';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array {
        $stmt = Database::connection()->prepare('SELECT * FROM students WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $student = $stmt->fetch();
        return $student ?: null;
    }

    public static function create(string $name, int $classId): void {
        $stmt = Database::connection()->prepare('INSERT INTO students (name, class_id) VALUES (:name, :class_id)');
        $stmt->execute(['name' => $name, 'class_id' => $classId]);
    }

    public static function update(int $id, string $name, int $classId): void {
        $stmt = Database::connection()->prepare('UPDATE students SET name = :name, class_id = :class_id WHERE id = :id');
        $stmt->execute(['id' => $id, 'name' => $name, 'class_id' => $classId]);
    }

    public static function delete(int $id): void {
        $stmt = Database::connection()->prepare('DELETE FROM students WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
