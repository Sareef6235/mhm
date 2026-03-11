<?php
require_once __DIR__ . '/../core/Database.php';

class ClassModel
{
    public static function all(): array
    {
        $stmt = Database::connection()->query('SELECT id, class_name FROM classes ORDER BY id');
        return $stmt->fetchAll();
    }

    public static function create(string $name): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO classes (class_name) VALUES (:name)');
        $stmt->execute(['name' => $name]);
    }

    public static function findIdByName(string $name): ?int
    {
        $stmt = Database::connection()->prepare('SELECT id FROM classes WHERE class_name = :class_name LIMIT 1');
        $stmt->execute(['class_name' => $name]);
        $row = $stmt->fetch();
        return $row ? (int)$row['id'] : null;
    }
}
