<?php

declare(strict_types=1);

namespace App\Repositories;

final class CourseRepository extends AbstractRepository
{
    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO courses (
                company_id, name, workload_hours, start_date, end_date, instructor_name, institution_name,
                certificate_prefix, program_syllabus, program_objectives, program_modules, program_methodology,
                program_evaluation, is_active
             ) VALUES (
                :company_id, :name, :workload_hours, :start_date, :end_date, :instructor_name, :institution_name,
                :certificate_prefix, :program_syllabus, :program_objectives, :program_modules, :program_methodology,
                :program_evaluation, :is_active
             )',
            [
                'company_id' => $data['company_id'],
                'name' => $data['name'],
                'workload_hours' => $data['workload_hours'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'instructor_name' => $data['instructor_name'],
                'institution_name' => $data['institution_name'],
                'certificate_prefix' => $data['certificate_prefix'] ?? 'CERT',
                'program_syllabus' => $data['program_syllabus'],
                'program_objectives' => $data['program_objectives'],
                'program_modules' => $data['program_modules'],
                'program_methodology' => $data['program_methodology'],
                'program_evaluation' => $data['program_evaluation'],
                'is_active' => $data['is_active'] ?? 1,
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->execute(
            'UPDATE courses
             SET name = :name,
                 workload_hours = :workload_hours,
                 start_date = :start_date,
                 end_date = :end_date,
                 instructor_name = :instructor_name,
                 institution_name = :institution_name,
                 certificate_prefix = :certificate_prefix,
                 program_syllabus = :program_syllabus,
                 program_objectives = :program_objectives,
                 program_modules = :program_modules,
                 program_methodology = :program_methodology,
                 program_evaluation = :program_evaluation,
                 is_active = :is_active,
                 updated_at = NOW()
             WHERE id = :id',
            [
                'id' => $id,
                'name' => $data['name'],
                'workload_hours' => $data['workload_hours'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'instructor_name' => $data['instructor_name'],
                'institution_name' => $data['institution_name'],
                'certificate_prefix' => $data['certificate_prefix'] ?? 'CERT',
                'program_syllabus' => $data['program_syllabus'],
                'program_objectives' => $data['program_objectives'],
                'program_modules' => $data['program_modules'],
                'program_methodology' => $data['program_methodology'],
                'program_evaluation' => $data['program_evaluation'],
                'is_active' => $data['is_active'] ?? 1,
            ]
        );
    }

    public function softDelete(int $id): void
    {
        $this->execute(
            'UPDATE courses SET deleted_at = NOW(), is_active = 0, updated_at = NOW() WHERE id = :id',
            ['id' => $id]
        );
    }

    public function findActive(int $id): ?array
    {
        $statement = $this->execute(
            'SELECT * FROM courses WHERE id = :id AND deleted_at IS NULL LIMIT 1',
            ['id' => $id]
        );

        return $statement->fetch() ?: null;
    }

    public function paginateByCompany(int $companyId, string $search = '', int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $filters = ['c.company_id = :company_id', 'c.deleted_at IS NULL'];
        $params = ['company_id' => $companyId];

        if ($search !== '') {
            $filters[] = '(c.name LIKE :search OR c.instructor_name LIKE :search OR c.institution_name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $where = implode(' AND ', $filters);
        $total = (int) $this->execute(
            'SELECT COUNT(*) FROM courses c WHERE ' . $where,
            $params
        )->fetchColumn();

        $items = $this->execute(
            'SELECT c.*,
                    SUM(CASE WHEN e.status = "active" THEN 1 ELSE 0 END) AS active_enrollments,
                    SUM(CASE WHEN e.status = "completed" THEN 1 ELSE 0 END) AS completed_enrollments
             FROM courses c
             LEFT JOIN enrollments e ON e.course_id = c.id
             WHERE ' . $where . '
             GROUP BY c.id
             ORDER BY c.start_date DESC, c.created_at DESC
             LIMIT ' . $perPage . ' OFFSET ' . $offset,
            $params
        )->fetchAll();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function allActiveByCompany(int $companyId): array
    {
        return $this->execute(
            'SELECT * FROM courses
             WHERE company_id = :company_id AND deleted_at IS NULL AND is_active = 1
             ORDER BY end_date DESC, name ASC',
            ['company_id' => $companyId]
        )->fetchAll();
    }

    public function countActiveEnrollments(int $courseId): int
    {
        return (int) $this->execute(
            'SELECT COUNT(*) FROM enrollments WHERE course_id = :course_id AND status = "active"',
            ['course_id' => $courseId]
        )->fetchColumn();
    }

    public function countByCompany(int $companyId): int
    {
        return (int) $this->execute(
            'SELECT COUNT(*) FROM courses
             WHERE company_id = :company_id AND deleted_at IS NULL',
            ['company_id' => $companyId]
        )->fetchColumn();
    }
}
