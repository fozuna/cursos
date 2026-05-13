ALTER TABLE students
    ADD COLUMN phone VARCHAR(20) NULL AFTER email,
    ADD COLUMN cpf VARCHAR(14) NULL AFTER phone,
    ADD COLUMN deleted_at DATETIME NULL AFTER updated_at,
    ADD UNIQUE KEY uk_students_company_email (company_id, email),
    ADD UNIQUE KEY uk_students_company_cpf (company_id, cpf),
    ADD KEY idx_students_deleted_at (deleted_at);

CREATE TABLE IF NOT EXISTS courses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    workload_hours SMALLINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    instructor_name VARCHAR(180) NOT NULL,
    institution_name VARCHAR(180) NOT NULL,
    certificate_prefix VARCHAR(20) DEFAULT 'CERT',
    program_syllabus TEXT NOT NULL,
    program_objectives TEXT NOT NULL,
    program_modules LONGTEXT NOT NULL,
    program_methodology TEXT NOT NULL,
    program_evaluation TEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    deleted_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_courses_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    KEY idx_courses_company_active (company_id, is_active),
    KEY idx_courses_deleted_at (deleted_at),
    KEY idx_courses_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS enrollments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    status ENUM('active', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
    enrolled_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_enrollments_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_enrollments_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE RESTRICT,
    CONSTRAINT fk_enrollments_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE RESTRICT,
    UNIQUE KEY uk_enrollments_student_course (student_id, course_id),
    KEY idx_enrollments_company_status (company_id, status),
    KEY idx_enrollments_course_status (course_id, status),
    KEY idx_enrollments_student_status (student_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
