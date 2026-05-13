<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

final class CourseCatalogService
{
    public function __construct(
        private readonly ProgramContentService $programContentService = new ProgramContentService()
    ) {
    }

    public function validateAndNormalize(array $payload, string $defaultInstitutionName): array
    {
        $name = trim((string) ($payload['name'] ?? ''));
        $workloadHours = (int) ($payload['workload_hours'] ?? 0);
        $startDate = trim((string) ($payload['start_date'] ?? ''));
        $endDate = trim((string) ($payload['end_date'] ?? ''));
        $instructorName = trim((string) ($payload['instructor_name'] ?? ''));
        $institutionName = trim((string) ($payload['institution_name'] ?? $defaultInstitutionName));
        $certificatePrefix = strtoupper(trim((string) ($payload['certificate_prefix'] ?? 'CERT')));
        $isActive = isset($payload['is_active']) ? 1 : 0;

        if (mb_strlen($name) < 3 || mb_strlen($name) > 180) {
            throw new InvalidArgumentException('O nome do curso deve ter entre 3 e 180 caracteres.');
        }

        if ($workloadHours <= 0 || $workloadHours > 2000) {
            throw new InvalidArgumentException('A carga horaria deve ser maior que zero e menor que 2000 horas.');
        }

        if (!$this->isValidDate($startDate) || !$this->isValidDate($endDate)) {
            throw new InvalidArgumentException('Informe datas validas para inicio e termino do curso.');
        }

        if ($startDate > $endDate) {
            throw new InvalidArgumentException('A data de inicio nao pode ser maior que a data de termino.');
        }

        if ($instructorName === '' || $institutionName === '') {
            throw new InvalidArgumentException('Instrutor e instituicao executora sao obrigatorios.');
        }

        if (!preg_match('/^[A-Z0-9-]{3,20}$/', $certificatePrefix)) {
            throw new InvalidArgumentException('O prefixo do certificado deve ter entre 3 e 20 caracteres alfanumericos.');
        }

        $program = $this->programContentService->buildFromPayload($payload);

        return [
            'name' => $name,
            'workload_hours' => $workloadHours,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'instructor_name' => $instructorName,
            'institution_name' => $institutionName,
            'certificate_prefix' => $certificatePrefix,
            'program_syllabus' => $program['sections']['syllabus'],
            'program_objectives' => $program['sections']['objectives'],
            'program_modules' => $program['sections']['modules'],
            'program_methodology' => $program['sections']['methodology'],
            'program_evaluation' => $program['sections']['evaluation'],
            'is_active' => $isActive,
        ];
    }

    public function buildProgramContentFromCourse(array $course): array
    {
        return $this->programContentService->buildFromSections([
            'syllabus' => (string) $course['program_syllabus'],
            'objectives' => (string) $course['program_objectives'],
            'modules' => (string) $course['program_modules'],
            'methodology' => (string) $course['program_methodology'],
            'evaluation' => (string) $course['program_evaluation'],
        ]);
    }

    private function isValidDate(string $value): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }

        [$year, $month, $day] = array_map('intval', explode('-', $value));

        return checkdate($month, $day, $year);
    }
}
