<?php

declare(strict_types=1);

namespace App\Repositories;

class PublicCertificateAccessTokenRepository extends AbstractRepository
{
    public function revokeActiveByCertificate(int $certificateId): void
    {
        $this->execute(
            'UPDATE public_certificate_access_tokens
             SET revoked_at = NOW()
             WHERE certificate_id = :certificate_id
               AND revoked_at IS NULL
               AND expires_at >= NOW()',
            ['certificate_id' => $certificateId]
        );
    }

    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO public_certificate_access_tokens (
                company_id, student_id, certificate_id, token_hash, expires_at
             ) VALUES (
                :company_id, :student_id, :certificate_id, :token_hash, :expires_at
             )',
            [
                'company_id' => $data['company_id'],
                'student_id' => $data['student_id'],
                'certificate_id' => $data['certificate_id'],
                'token_hash' => $data['token_hash'],
                'expires_at' => $data['expires_at'],
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function touchLastUsed(int $id): void
    {
        $this->execute(
            'UPDATE public_certificate_access_tokens
             SET last_used_at = NOW()
             WHERE id = :id',
            ['id' => $id]
        );
    }

    public function findValidByTokenHash(string $tokenHash): ?array
    {
        $statement = $this->execute(
            'SELECT
                t.*,
                c.company_id AS certificate_company_id,
                c.student_id AS certificate_student_id,
                c.course_name,
                c.workload_hours,
                c.completion_date,
                c.certificate_code,
                c.institution_name,
                c.validation_hash,
                c.pdf_path,
                c.status AS certificate_status,
                s.full_name,
                s.phone,
                co.name AS company_name
             FROM public_certificate_access_tokens t
             INNER JOIN certificates c ON c.id = t.certificate_id
             INNER JOIN students s ON s.id = t.student_id
             INNER JOIN companies co ON co.id = t.company_id
             WHERE t.token_hash = :token_hash
               AND t.revoked_at IS NULL
               AND t.expires_at >= NOW()
               AND c.status = "generated"
               AND c.pdf_path IS NOT NULL
             LIMIT 1',
            ['token_hash' => $tokenHash]
        );

        return $statement->fetch() ?: null;
    }

    public function findCertificateForSharing(int $certificateId, int $companyId): ?array
    {
        $statement = $this->execute(
            'SELECT
                c.id,
                c.company_id,
                c.student_id,
                c.course_name,
                c.certificate_code,
                c.pdf_path,
                c.status,
                s.full_name,
                s.phone
             FROM certificates c
             INNER JOIN students s ON s.id = c.student_id
             WHERE c.id = :certificate_id
               AND c.company_id = :company_id
               AND c.status = "generated"
               AND c.pdf_path IS NOT NULL
             LIMIT 1',
            [
                'certificate_id' => $certificateId,
                'company_id' => $companyId,
            ]
        );

        return $statement->fetch() ?: null;
    }
}
