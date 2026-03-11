CREATE DATABASE IF NOT EXISTS hvernued_range CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hvernued_range;

CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(150) NOT NULL,
    class VARCHAR(10) NOT NULL,
    gender ENUM('Male','Female') NOT NULL,
    class_number INT NOT NULL,
    phone VARCHAR(30) NOT NULL,
    created_at DATETIME NOT NULL,
    UNIQUE KEY uniq_class_gender_number (class, gender, class_number)
);

CREATE TABLE IF NOT EXISTS textbooks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_name VARCHAR(150) NOT NULL,
    class VARCHAR(10) NOT NULL,
    price DECIMAL(10,2) NOT NULL
);

CREATE TABLE IF NOT EXISTS notebooks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notebook_type VARCHAR(100) NOT NULL,
    pages INT NOT NULL,
    price DECIMAL(10,2) NOT NULL
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(150) NOT NULL,
    class VARCHAR(10) NOT NULL,
    gender ENUM('Male','Female') NOT NULL,
    class_number INT NOT NULL,
    phone VARCHAR(30) NOT NULL,
    item_type ENUM('textbook','notebook') NOT NULL,
    item_name VARCHAR(150) NOT NULL,
    pages INT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_class (class),
    INDEX idx_gender (gender),
    INDEX idx_item_name (item_name),
    INDEX idx_order_date (order_date)
);

INSERT INTO admin (username, password)
VALUES ('admin', '$2y$12$k4jxSd9qC9Ct2pAIohB/pegFn1CyiEDMdRMvu/zwfjx0Sti7n/Mge')
ON DUPLICATE KEY UPDATE username=VALUES(username);

TRUNCATE TABLE textbooks;
INSERT INTO textbooks (book_name, class, price) VALUES
('Fiqh', '1', 90),('Arabic', '1', 80),('Nahvu', '1', 70),
('Fiqh', '2', 100),('Arabic', '2', 90),('Quran', '2', 120),
('Hadith', '3', 95),('Tajweed', '3', 85),
('Aqaid', '4', 88),('Sirat', '4', 75),
('Arabic Grammar', '5', 85),('Nahvu Advanced', '5', 78),
('Balagat', '6', 86),('Tafsir', '6', 110),
('Usul Fiqh', '7', 120),('Logic', '7', 105),
('Hadith Usul', '8', 130),('Faraid', '8', 98),
('Tafsir Advanced', '9', 140),('Arabic Lit', '9', 125),
('Mantiq', '10', 145),('Philosophy', '10', 135),
('Fiqh Special', '11', 150),('Hadith Special', '11', 160),
('Dawrah Text', '12', 170),('Research Basics', '12', 165);

TRUNCATE TABLE notebooks;
INSERT INTO notebooks (notebook_type, pages, price) VALUES
('Notebook',100,30),('Notebook',200,50),('Notebook',300,70);
