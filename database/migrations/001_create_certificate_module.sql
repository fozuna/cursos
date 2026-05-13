CREATE TABLE IF NOT EXISTS companies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    logo_path VARCHAR(255) DEFAULT NULL,
    primary_color VARCHAR(20) NOT NULL DEFAULT '#1d4ed8',
    secondary_color VARCHAR(20) NOT NULL DEFAULT '#0f172a',
    theme_mode ENUM('light', 'dark') NOT NULL DEFAULT 'light',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS certificate_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(140) NOT NULL,
    slug VARCHAR(140) NOT NULL,
    theme_mode ENUM('light', 'dark') NOT NULL DEFAULT 'light',
    background_type ENUM('clean', 'executive', 'premium') NOT NULL DEFAULT 'premium',
    settings_json JSON NOT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_templates_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE KEY uk_template_company_slug (company_id, slug),
    KEY idx_template_company_default (company_id, is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS students (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    full_name VARCHAR(180) NOT NULL,
    email VARCHAR(180) DEFAULT NULL,
    document_number VARCHAR(40) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_students_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    KEY idx_students_company_name (company_id, full_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS generation_batches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    import_source ENUM('manual', 'csv', 'xlsx') NOT NULL DEFAULT 'manual',
    source_file VARCHAR(255) DEFAULT NULL,
    total_items INT UNSIGNED NOT NULL DEFAULT 0,
    processed_items INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('queued', 'processing', 'completed', 'failed') NOT NULL DEFAULT 'queued',
    started_at DATETIME DEFAULT NULL,
    finished_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_batches_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    KEY idx_batches_company_status (company_id, status),
    KEY idx_batches_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS certificates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    template_id BIGINT UNSIGNED NOT NULL,
    batch_id BIGINT UNSIGNED DEFAULT NULL,
    course_name VARCHAR(180) NOT NULL,
    workload_hours SMALLINT UNSIGNED NOT NULL,
    completion_date DATE NOT NULL,
    certificate_code VARCHAR(80) NOT NULL,
    instructor_name VARCHAR(180) NOT NULL,
    institution_name VARCHAR(180) NOT NULL,
    program_content LONGTEXT NOT NULL,
    validation_hash CHAR(64) NOT NULL,
    validation_url VARCHAR(255) NOT NULL,
    pdf_path VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'generated', 'failed') NOT NULL DEFAULT 'pending',
    metadata_json JSON DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_certificates_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_certificates_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_certificates_template FOREIGN KEY (template_id) REFERENCES certificate_templates(id) ON DELETE RESTRICT,
    CONSTRAINT fk_certificates_batch FOREIGN KEY (batch_id) REFERENCES generation_batches(id) ON DELETE SET NULL,
    UNIQUE KEY uk_certificates_code (certificate_code),
    UNIQUE KEY uk_certificates_hash (validation_hash),
    KEY idx_certificates_company_status (company_id, status),
    KEY idx_certificates_completion_date (completion_date),
    KEY idx_certificates_batch (batch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS generation_histories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    batch_id BIGINT UNSIGNED DEFAULT NULL,
    certificate_id BIGINT UNSIGNED DEFAULT NULL,
    action VARCHAR(80) NOT NULL,
    status ENUM('info', 'success', 'warning', 'error') NOT NULL DEFAULT 'info',
    message VARCHAR(255) NOT NULL,
    payload_json JSON DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_histories_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_histories_batch FOREIGN KEY (batch_id) REFERENCES generation_batches(id) ON DELETE SET NULL,
    CONSTRAINT fk_histories_certificate FOREIGN KEY (certificate_id) REFERENCES certificates(id) ON DELETE SET NULL,
    KEY idx_histories_company_date (company_id, created_at),
    KEY idx_histories_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO companies (id, name, slug, primary_color, secondary_color, theme_mode)
VALUES (1, 'Academia Corporativa Premium', 'academia-corporativa-premium', '#1d4ed8', '#0f172a', 'light')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    primary_color = VALUES(primary_color),
    secondary_color = VALUES(secondary_color),
    theme_mode = VALUES(theme_mode);

INSERT INTO certificate_templates (company_id, name, slug, theme_mode, background_type, settings_json, is_default)
VALUES (
    1,
    'Premium Executivo',
    'premium-executivo',
    'light',
    'premium',
    JSON_OBJECT(
        'accentColor', '#1d4ed8',
        'title', 'Certificado de Conclusao',
        'subtitle', 'Reconhecimento oficial de participacao e aproveitamento',
        'footer', 'Documento assinado digitalmente e validavel por QR Code'
    ),
    1
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    settings_json = VALUES(settings_json),
    is_default = VALUES(is_default);
