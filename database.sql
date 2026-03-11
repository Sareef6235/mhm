CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    class_id INT NOT NULL,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS prayer_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    date DATE NOT NULL,
    subah TINYINT(1) NOT NULL DEFAULT 0,
    dhuhr TINYINT(1) NOT NULL DEFAULT 0,
    asr TINYINT(1) NOT NULL DEFAULT 0,
    maghrib TINYINT(1) NOT NULL DEFAULT 0,
    isha TINYINT(1) NOT NULL DEFAULT 0,
    salawat INT NOT NULL DEFAULT 0,
    total_points INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_daily_student (student_id, date),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

INSERT INTO classes (class_name)
SELECT * FROM (SELECT 'Class 1' AS class_name) AS t
WHERE NOT EXISTS (SELECT 1 FROM classes WHERE class_name = 'Class 1');

INSERT INTO classes (class_name)
SELECT * FROM (SELECT 'Class 2' AS class_name) AS t
WHERE NOT EXISTS (SELECT 1 FROM classes WHERE class_name = 'Class 2');
