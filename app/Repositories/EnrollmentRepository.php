<?php

declare(strict_types=1);

namespace App\Repositories;

final class EnrollmentRepository extends AbstractRepository
{
    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO enrollments (company_id, student_id, course_id, status, enrolled_at, completed_at)
             VALUES (:company_id, :student_id, :course_id, :status, :enrolled_at, :completed_at)',
            [
                'company_id' => $data['company_id'],
                'student_id' => $data['student_id'],
                'course_id' => $data['course_id'],
                'status' => $data['status'] ?? 'active',
                'enrolled_at' => $data['enrolled_at'],
                'completed_at' => $data['completed_at'] ?? null,
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function updateStatus(int $id, string $status, ?string $completedAt): void
    {
        $this->execute(
            'UPDATE enrollments
             SET status = :status,
                 completed_at = :completed_at,
                 updated_at = NOW()
             WHERE id = :id',
            [
                'id' => $id,
                'status' => $status,
                'completed_at' => $completedAt,
            ]
        );
    }

    public function existsForStudentAndCourse(int $studentId, int $courseId): bool
    {
        $statement = $this->execute(
            'SELECT 1 FROM enrollments WHERE student_id = :student_id AND course_id = :course_id LIMIT 1',
            ['student_id' => $studentId, 'course_id' => $courseId]
        );

        return (bool) $statement->fetchColumn();
    }

    public function countByStudent(int $studentId): int
    {
        return (int) $this->execute(
            'SELECT COUNT(*) FROM enrollments WHERE student_id = :student_id',
            ['student_id' => $studentId]
        )->fetchColumn();
    }

    public function paginateByCompany(int $companyId, array $filters, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['e.company_id = :company_id', 's.deleted_at IS NULL', 'c.deleted_at IS NULL'];
        $params = ['company_id' => $companyId];

        if (!empty($filters['search'])) {
            $where[] = '(s.full_name LIKE :search OR c.name LIKE :search OR s.email LIKE :search OR s.cpf LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['status'])) {
            $where[] = 'e.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['course_id'])) {
            $where[] = 'e.course_id = :course_id';
            $params['course_id'] = (int) $filters['course_id'];
        }

        if (!empty($filters['student_id'])) {
            $where[] = 'e.student_id = :student_id';
            $params['student_id'] = (int) $filters['student_id'];
        }

        $whereSql = implode(' AND ', $where);
        $baseSelect = ' FROM enrollments e
            INNER JOIN students s ON s.id = e.student_id
            INNER JOIN courses c ON c.id = e.course_id
            WHERE ' . $whereSql;

        $total = (int) $this->execute('SELECT COUNT(*)' . $baseSelect, $params)->fetchColumn();
        $items = $this->execute(
            'SELECT e.*, s.full_name, s.email, s.phone, s.cpf, c.name AS course_name, c.workload_hours, c.end_date' . $baseSelect . '
             ORDER BY e.enrolled_at DESC, e.id DESC
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

    public function byCourse(int $courseId): array
    {
        return $this->execute(
            'SELECT e.*, s.full_name, s.email, s.phone, s.cpf
             FROM enrollments e
             INNER JOIN students s ON s.id = e.student_id
             WHERE e.course_id = :course_id AND s.deleted_at IS NULL
             ORDER BY s.full_name ASC',
            ['course_id' => $courseId]
        )->fetchAll();
    }

    public function byStudent(int $studentId): array
    {
        return $this->execute(
            'SELECT e.*, c.name AS course_name, c.start_date, c.end_date, c.workload_hours
             FROM enrollments e
             INNER JOIN courses c ON c.id = e.course_id
             WHERE e.student_id = :student_id AND c.deleted_at IS NULL
             ORDER BY c.end_date DESC, e.created_at DESC',
            ['student_id' => $studentId]
        )->fetchAll();
    }

    public function completedByCourse(int $courseId): array
    {
        return $this->execute(
            'SELECT e.*, s.full_name, s.email, s.phone, s.cpf
             FROM enrollments e
             INNER JOIN students s ON s.id = e.student_id
             WHERE e.course_id = :course_id
               AND e.status = "completed"
               AND s.deleted_at IS NULL
             ORDER BY s.full_name ASC',
            ['course_id' => $courseId]
        )->fetchAll();
    }

    public function countByCompany(int $companyId): int
    {
        return (int) $this->execute(
            'SELECT COUNT(*) FROM enrollments WHERE company_id = :company_id',
            ['company_id' => $companyId]
        )->fetchColumn();
    }
}
