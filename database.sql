CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    dob DATE NOT NULL,
    password VARCHAR(255) NOT NULL,
    class_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS prayer_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    date DATE NOT NULL,
    subah TINYINT(1) NOT NULL DEFAULT 0,
    dhuhr TINYINT(1) NOT NULL DEFAULT 0,
    asr TINYINT(1) NOT NULL DEFAULT 0,
    maghrib TINYINT(1) NOT NULL DEFAULT 0,
    isha TINYINT(1) NOT NULL DEFAULT 0,
    salawat INT NOT NULL DEFAULT 0,
    points INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_daily_student (student_id, date),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

INSERT IGNORE INTO classes (class_name) VALUES
('Class 1'),('Class 2'),('Class 3'),('Class 4'),('Class 5'),('Class 6'),
('Class 7'),('Class 8'),('Class 9'),('Class 10'),('Class 11'),('Class 12');
