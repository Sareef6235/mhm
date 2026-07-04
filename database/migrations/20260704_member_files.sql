CREATE TABLE IF NOT EXISTS member_files(
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    member_id BIGINT NOT NULL,
    category ENUM('profile','signature','documents','certificates','payments','attendance','gallery','other') NOT NULL DEFAULT 'documents',
    file_name VARCHAR(190) NOT NULL,
    original_file_name VARCHAR(190) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(120),
    file_extension VARCHAR(20),
    file_size BIGINT DEFAULT 0,
    uploaded_by INT NULL,
    status ENUM('active','archived','deleted') DEFAULT 'active',
    notes TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_member_files(member_id,category,status),
    FOREIGN KEY(member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY(uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
