<?php
require_once __DIR__ . '/../core/Database.php';

class AdminModel
{
    public static function findByUsername(string $username): ?array
    {
        $stmt = Database::connection()->prepare('SELECT id, username, password FROM admins WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch();
        return $admin ?: null;
    }
}
