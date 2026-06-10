<?php
/**
 * cPanel-ready MySQLi connection file.
 * Update these values in cPanel > MySQL Databases before uploading.
 */
$host = 'localhost';
$user = 'DATABASE_USER';
$pass = 'DATABASE_PASSWORD';
$dbname = 'DATABASE_NAME';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die('Database Connection Failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
