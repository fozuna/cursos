<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

final class CertificateIssueIdentityService
{
    public function buildIdentifier(int $companyId, array $row): string
    {
        $fullName = $this->normalizeText((string) ($row['full_name'] ?? ''));
        $courseName = $this->normalizeText((string) ($row['course_name'] ?? ''));
        $completionDate = trim((string) ($row['completion_date'] ?? ''));
        $workloadHours = (int) ($row['workload_hours'] ?? 0);
        $documentNumber = preg_replace('/\D+/', '', (string) ($row['document_number'] ?? $row['cpf'] ?? '')) ?: '';
        $studentId = isset($row['student_id']) ? (int) $row['student_id'] : 0;

        if ($fullName === '' || $courseName === '' || $completionDate === '' || $workloadHours <= 0) {
            throw new InvalidArgumentException('Nao foi possivel calcular a identidade unica do certificado para verificar reemissao.');
        }

        return hash('sha256', implode('|', [
            $companyId,
            $studentId > 0 ? 'student:' . $studentId : 'student:0',
            $documentNumber !== '' ? 'document:' . $documentNumber : 'document:none',
            'name:' . $fullName,
            'course:' . $courseName,
            'completion:' . $completionDate,
            'workload:' . $workloadHours,
        ]));
    }

    private function normalizeText(string $value): string
    {
        $value = trim(mb_strtolower($value));
        $value = preg_replace('/\s+/', ' ', $value) ?: '';

        if (function_exists('transliterator_transliterate')) {
            $transliterated = transliterator_transliterate('Any-Latin; Latin-ASCII', $value);

            if (is_string($transliterated) && $transliterated !== '') {
                $value = $transliterated;
            }
        }

        return $value;
    }
}
