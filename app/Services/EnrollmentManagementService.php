<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\StudentRepository;
use InvalidArgumentException;

final class EnrollmentManagementService
{
    public function __construct(
        private readonly EnrollmentRepository $enrollmentRepository = new EnrollmentRepository(),
        private readonly CourseRepository $courseRepository = new CourseRepository(),
        private readonly StudentRepository $studentRepository = new StudentRepository()
    ) {
    }

    /**
     * @param array<int, int|string> $studentIds
     * @return array{created: int, skipped: int}
     */
    public function enrollMany(int $companyId, int $courseId, array $studentIds, string $status, string $enrolledAt, ?string $completedAt): array
    {
        $course = $this->courseRepository->findActive($courseId);

        if ($course === null) {
            throw new InvalidArgumentException('Curso nao encontrado para matricula.');
        }

        if (!$this->isValidDateTime($enrolledAt)) {
            throw new InvalidArgumentException('Informe uma data de matricula valida.');
        }

        $enrolledAt = $this->normalizeDateTime($enrolledAt);

        if ($status === 'completed' && ($completedAt === null || !$this->isValidDateTime($completedAt))) {
            throw new InvalidArgumentException('Informe a data de conclusao para matriculas concluidas.');
        }

        $completedAt = $status === 'completed' ? $this->normalizeDateTime((string) $completedAt) : null;

        $created = 0;
        $skipped = 0;

        foreach (array_unique(array_map('intval', $studentIds)) as $studentId) {
            if ($studentId <= 0) {
                continue;
            }

            if ($this->studentRepository->findActive($studentId) === null) {
                $skipped++;
                continue;
            }

            if ($this->enrollmentRepository->existsForStudentAndCourse($studentId, $courseId)) {
                $skipped++;
                continue;
            }

            $this->enrollmentRepository->create([
                'company_id' => $companyId,
                'student_id' => $studentId,
                'course_id' => $courseId,
                'status' => $status,
                'enrolled_at' => $enrolledAt,
                'completed_at' => $completedAt,
            ]);
            $created++;
        }

        if ($created === 0 && $skipped > 0) {
            throw new InvalidArgumentException('Nenhuma matricula nova foi criada. Verifique duplicidades ou alunos inativos.');
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    public function validateStatusUpdate(string $status, ?string $completedAt): array
    {
        if (!in_array($status, ['active', 'completed', 'cancelled'], true)) {
            throw new InvalidArgumentException('Status de matricula invalido.');
        }

        if ($status === 'completed') {
            if ($completedAt === null || !$this->isValidDateTime($completedAt)) {
                throw new InvalidArgumentException('Informe uma data de conclusao valida.');
            }
            $completedAt = $this->normalizeDateTime($completedAt);
        } else {
            $completedAt = null;
        }

        return [
            'status' => $status,
            'completed_at' => $completedAt,
        ];
    }

    private function isValidDateTime(string $value): bool
    {
        return strtotime($value) !== false;
    }

    private function normalizeDateTime(string $value): string
    {
        return date('Y-m-d H:i:s', strtotime($value));
    }
}
