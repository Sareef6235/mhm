CREATE DATABASE IF NOT EXISTS madrasa_books CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE madrasa_books;

CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_name VARCHAR(150) NOT NULL,
    class VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(150) NOT NULL,
    class VARCHAR(50) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    book_name VARCHAR(150) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_student_name (student_name),
    INDEX idx_phone (phone),
    INDEX idx_class (class),
    INDEX idx_book_name (book_name)
);

CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO admin (username, password)
VALUES ('admin', '$2y$12$k4jxSd9qC9Ct2pAIohB/pegFn1CyiEDMdRMvu/zwfjx0Sti7n/Mge')
ON DUPLICATE KEY UPDATE username = VALUES(username);

TRUNCATE TABLE books;
INSERT INTO books (book_name, class, price) VALUES
('Fiqh', '1', 90),
('Arabic Basics', '1', 80),
('Nahvu Intro', '1', 70),
('Quran', '2', 120),
('Aqaid', '2', 85),
('Hadith', '3', 95),
('Tajweed', '4', 75),
('Arabic Grammar', '5', 85),
('Sirat', '6', 78),
('Nahvu Advanced', '7', 60),
('Balagat', '8', 65),
('Tafsir', '9', 110),
('Arabic', '10', 50),
('Usul al-Fiqh', '11', 130),
('Mantiq', '12', 140);
