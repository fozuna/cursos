CREATE TABLE IF NOT EXISTS public_certificate_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    certificate_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    revoked_at DATETIME DEFAULT NULL,
    last_used_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_public_access_tokens_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_public_access_tokens_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_public_access_tokens_certificate FOREIGN KEY (certificate_id) REFERENCES certificates(id) ON DELETE CASCADE,
    UNIQUE KEY uk_public_access_tokens_hash (token_hash),
    KEY idx_public_access_tokens_certificate_active (certificate_id, revoked_at, expires_at),
    KEY idx_public_access_tokens_student (student_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS public_certificate_rate_limits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    failed_attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    first_failed_at DATETIME DEFAULT NULL,
    blocked_until DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_public_certificate_rate_limits_ip (ip_address),
    KEY idx_public_certificate_rate_limits_blocked (blocked_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS public_certificate_access_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED DEFAULT NULL,
    student_id BIGINT UNSIGNED DEFAULT NULL,
    certificate_id BIGINT UNSIGNED DEFAULT NULL,
    access_token_id BIGINT UNSIGNED DEFAULT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    action VARCHAR(40) NOT NULL,
    status ENUM('info', 'success', 'blocked', 'denied', 'error') NOT NULL DEFAULT 'info',
    message VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_public_access_logs_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL,
    CONSTRAINT fk_public_access_logs_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL,
    CONSTRAINT fk_public_access_logs_certificate FOREIGN KEY (certificate_id) REFERENCES certificates(id) ON DELETE SET NULL,
    CONSTRAINT fk_public_access_logs_token FOREIGN KEY (access_token_id) REFERENCES public_certificate_access_tokens(id) ON DELETE SET NULL,
    KEY idx_public_access_logs_date (created_at),
    KEY idx_public_access_logs_action (action),
    KEY idx_public_access_logs_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
