CREATE DATABASE IF NOT EXISTS exam_results;
USE exam_results;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(120) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS exam_settings (
  id INT PRIMARY KEY,
  total_marks INT NOT NULL DEFAULT 0,
  pass_mark INT NOT NULL DEFAULT 0,
  grade_system JSON NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS certificate_templates (
  id INT PRIMARY KEY,
  title VARCHAR(255),
  footer VARCHAR(255),
  logo_url TEXT,
  principal_name VARCHAR(255),
  signature_url TEXT,
  background_url TEXT,
  watermark VARCHAR(255),
  fields_json JSON,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS results (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  register_number VARCHAR(120) NOT NULL,
  student_name VARCHAR(255) NOT NULL,
  school_name VARCHAR(255) NOT NULL,
  photo_url TEXT,
  dob DATE NOT NULL,
  subjects_json JSON NOT NULL,
  total_marks DECIMAL(10,2) NOT NULL,
  percentage DECIMAL(5,2) NOT NULL,
  grade VARCHAR(20) NOT NULL,
  status ENUM('PASS','FAIL') NOT NULL,
  short_code VARCHAR(12) UNIQUE NOT NULL,
  is_enabled TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_reg (register_number),
  INDEX idx_short (short_code)
);

CREATE TABLE IF NOT EXISTS search_analytics (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  register_number VARCHAR(120),
  found TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin password is `admin123` (change immediately in production).
INSERT INTO admins (username, password_hash)
VALUES ('admin', '$2a$10$Q37.wM6ewm5gs9ZiQ5Q2nOn6L0vaNvKcJsteVEh9UpAJZxkd06P88')
ON DUPLICATE KEY UPDATE username = username;
