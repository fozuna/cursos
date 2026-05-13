<?php

declare(strict_types=1);

namespace App\Repositories;

final class CertificateRepository extends AbstractRepository
{
    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO certificates (
                company_id, student_id, template_id, batch_id, course_name, workload_hours, completion_date,
                certificate_code, instructor_name, institution_name, program_content, validation_hash, validation_url, pdf_path, status, metadata_json
             ) VALUES (
                :company_id, :student_id, :template_id, :batch_id, :course_name, :workload_hours, :completion_date,
                :certificate_code, :instructor_name, :institution_name, :program_content, :validation_hash, :validation_url, :pdf_path, :status, :metadata_json
             )',
            [
                'company_id' => $data['company_id'],
                'student_id' => $data['student_id'],
                'template_id' => $data['template_id'],
                'batch_id' => $data['batch_id'] ?? null,
                'course_name' => $data['course_name'],
                'workload_hours' => $data['workload_hours'],
                'completion_date' => $data['completion_date'],
                'certificate_code' => $data['certificate_code'],
                'instructor_name' => $data['instructor_name'],
                'institution_name' => $data['institution_name'],
                'program_content' => $data['program_content'],
                'validation_hash' => $data['validation_hash'],
                'validation_url' => $data['validation_url'],
                'pdf_path' => $data['pdf_path'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'metadata_json' => $data['metadata_json'] ?? null,
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function markGenerated(int $id, string $pdfPath): void
    {
        $this->execute(
            'UPDATE certificates SET pdf_path = :pdf_path, status = "generated", updated_at = NOW() WHERE id = :id',
            ['pdf_path' => $pdfPath, 'id' => $id]
        );
    }

    public function markFailed(int $id): void
    {
        $this->execute(
            'UPDATE certificates SET status = "failed", updated_at = NOW() WHERE id = :id',
            ['id' => $id]
        );
    }

    public function existsByCode(string $certificateCode): bool
    {
        $statement = $this->execute(
            'SELECT 1 FROM certificates WHERE certificate_code = :certificate_code LIMIT 1',
            ['certificate_code' => $certificateCode]
        );

        return (bool) $statement->fetchColumn();
    }

    public function nextSequenceForYear(int $year): int
    {
        $statement = $this->execute(
            'SELECT MAX(CAST(SUBSTRING_INDEX(certificate_code, "-", -1) AS UNSIGNED)) AS max_sequence
             FROM certificates
             WHERE certificate_code REGEXP :pattern',
            ['pattern' => sprintf('^CERT-%d-[0-9]{4,}$', $year)]
        );

        return (int) ($statement->fetchColumn() ?: 0) + 1;
    }

    public function findByHash(string $hash): ?array
    {
        $statement = $this->execute(
            'SELECT c.*, s.full_name, s.email, t.name AS template_name, co.name AS company_name
             FROM certificates c
             INNER JOIN students s ON s.id = c.student_id
             INNER JOIN certificate_templates t ON t.id = c.template_id
             INNER JOIN companies co ON co.id = c.company_id
             WHERE c.validation_hash = :hash
             LIMIT 1',
            ['hash' => $hash]
        );

        return $statement->fetch() ?: null;
    }

    public function statsByCompany(int $companyId): array
    {
        $statement = $this->execute(
            'SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = "generated" THEN 1 ELSE 0 END) AS generated_count,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) AS pending_count,
                COUNT(DISTINCT course_name) AS unique_courses
             FROM certificates
             WHERE company_id = :company_id',
            ['company_id' => $companyId]
        );

        return $statement->fetch() ?: [
            'total' => 0,
            'generated_count' => 0,
            'pending_count' => 0,
            'unique_courses' => 0,
        ];
    }

    public function latestByCompany(int $companyId, int $limit = 12): array
    {
        return $this->execute(
            'SELECT c.*, s.full_name
             FROM certificates c
             INNER JOIN students s ON s.id = c.student_id
             WHERE c.company_id = :company_id
             ORDER BY c.created_at DESC
             LIMIT ' . (int) $limit,
            ['company_id' => $companyId]
        )->fetchAll();
    }

    public function generatedFilesByBatch(int $batchId): array
    {
        return $this->execute(
            'SELECT certificate_code, pdf_path FROM certificates
             WHERE batch_id = :batch_id AND status = "generated" AND pdf_path IS NOT NULL
             ORDER BY id ASC',
            ['batch_id' => $batchId]
        )->fetchAll();
    }
}
