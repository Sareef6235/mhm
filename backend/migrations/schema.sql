CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS exams (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  total_marks INT UNSIGNED NOT NULL,
  pass_mark INT UNSIGNED NOT NULL,
  grading_config JSON NOT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS students (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  exam_id INT UNSIGNED NOT NULL,
  register_number VARCHAR(80) NOT NULL,
  name VARCHAR(200) NOT NULL,
  dob DATE NULL,
  photo_url VARCHAR(500) NULL,
  subjects JSON NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  percentage DECIMAL(5,2) NOT NULL,
  grade VARCHAR(10) NOT NULL,
  result_status ENUM('PASS','FAIL') NOT NULL,
  search_count INT UNSIGNED DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_students_exam_id (exam_id),
  INDEX idx_students_register_number (register_number),
  CONSTRAINT fk_students_exam FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS result_links (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  exam_id INT UNSIGNED NOT NULL,
  short_code VARCHAR(10) NOT NULL UNIQUE,
  is_enabled TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_result_links_exam FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS certificate_templates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  exam_id INT UNSIGNED NOT NULL,
  logo_url VARCHAR(500),
  background_url VARCHAR(500),
  principal_name VARCHAR(255),
  signature_url VARCHAR(500),
  certificate_title VARCHAR(255),
  footer_text TEXT,
  layout_config JSON NOT NULL,
  CONSTRAINT fk_certificate_exam FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
);
