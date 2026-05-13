ALTER TABLE certificates
    ADD COLUMN program_content LONGTEXT NOT NULL AFTER institution_name;
