CREATE DATABASE IF NOT EXISTS monthly_plan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE monthly_plan;

CREATE TABLE IF NOT EXISTS ustads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_ustad_name (name)
);

CREATE TABLE IF NOT EXISTS weeks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    week_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_week_name (week_name)
);

INSERT IGNORE INTO weeks (week_name) VALUES
('Week 1'),
('Week 2'),
('Week 3'),
('Week 4');
