<?php
require_once __DIR__ . '/../core/Database.php';

class ClassModel {
    public static function all(): array {
        $stmt = Database::connection()->query('SELECT id, class_name FROM classes ORDER BY class_name');
        return $stmt->fetchAll();
    }

    public static function create(string $name): void {
        $stmt = Database::connection()->prepare('INSERT INTO classes (class_name) VALUES (:name)');
        $stmt->execute(['name' => $name]);
    }
}
