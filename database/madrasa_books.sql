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

INSERT INTO books (book_name, class, price) VALUES
('Fiqh', 'I', 90),
('Quran', 'I', 120),
('Aqaid', 'II', 80),
('Hadith', 'III', 95),
('Tajweed', 'IV', 70),
('Arabic Grammar', 'V', 85),
('Sirat', 'VI', 75),
('Nahvu', 'VII', 60),
('Balagat', 'VIII', 65),
('Tafsir', 'IX', 110),
('Arabic', 'X', 50)
ON DUPLICATE KEY UPDATE price = VALUES(price);
