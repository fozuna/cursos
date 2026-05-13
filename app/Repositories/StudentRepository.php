<?php

declare(strict_types=1);

namespace App\Repositories;

class StudentRepository extends AbstractRepository
{
    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO students (company_id, full_name, email, phone, cpf, document_number)
             VALUES (:company_id, :full_name, :email, :phone, :cpf, :document_number)',
            [
                'company_id' => $data['company_id'],
                'full_name' => $data['full_name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'cpf' => $data['cpf'] ?? null,
                'document_number' => $data['document_number'] ?? null,
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->execute(
            'UPDATE students
             SET full_name = :full_name,
                 email = :email,
                 phone = :phone,
                 cpf = :cpf,
                 document_number = :document_number,
                 updated_at = NOW()
             WHERE id = :id',
            [
                'id' => $id,
                'full_name' => $data['full_name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'cpf' => $data['cpf'] ?? null,
                'document_number' => $data['document_number'] ?? null,
            ]
        );
    }

    public function softDelete(int $id): void
    {
        $this->execute(
            'UPDATE students SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id',
            ['id' => $id]
        );
    }

    public function find(int $id): ?array
    {
        $statement = $this->execute(
            'SELECT * FROM students WHERE id = :id LIMIT 1',
            ['id' => $id]
        );

        return $statement->fetch() ?: null;
    }

    public function findActive(int $id): ?array
    {
        $statement = $this->execute(
            'SELECT * FROM students WHERE id = :id AND deleted_at IS NULL LIMIT 1',
            ['id' => $id]
        );

        return $statement->fetch() ?: null;
    }

    public function existsByEmail(int $companyId, string $email, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT 1 FROM students
                WHERE company_id = :company_id
                  AND email = :email
                  AND deleted_at IS NULL';
        $params = [
            'company_id' => $companyId,
            'email' => $email,
        ];

        if ($ignoreId !== null) {
            $sql .= ' AND id <> :ignore_id';
            $params['ignore_id'] = $ignoreId;
        }

        $statement = $this->execute($sql . ' LIMIT 1', $params);

        return (bool) $statement->fetchColumn();
    }

    public function existsByCpf(int $companyId, string $cpf, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT 1 FROM students
                WHERE company_id = :company_id
                  AND cpf = :cpf
                  AND deleted_at IS NULL';
        $params = [
            'company_id' => $companyId,
            'cpf' => $cpf,
        ];

        if ($ignoreId !== null) {
            $sql .= ' AND id <> :ignore_id';
            $params['ignore_id'] = $ignoreId;
        }

        $statement = $this->execute($sql . ' LIMIT 1', $params);

        return (bool) $statement->fetchColumn();
    }

    public function paginateByCompany(int $companyId, string $search = '', int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $filters = ['company_id = :company_id', 'deleted_at IS NULL'];
        $params = ['company_id' => $companyId];

        if ($search !== '') {
            $filters[] = '(full_name LIKE :search OR email LIKE :search OR phone LIKE :search OR cpf LIKE :search OR document_number LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $where = implode(' AND ', $filters);
        $total = (int) $this->execute(
            'SELECT COUNT(*) FROM students WHERE ' . $where,
            $params
        )->fetchColumn();

        $items = $this->execute(
            'SELECT * FROM students WHERE ' . $where . ' ORDER BY created_at DESC LIMIT ' . $perPage . ' OFFSET ' . $offset,
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
            'SELECT * FROM students
             WHERE company_id = :company_id AND deleted_at IS NULL
             ORDER BY full_name ASC',
            ['company_id' => $companyId]
        )->fetchAll();
    }

    public function countByCompany(int $companyId): int
    {
        return (int) $this->execute(
            'SELECT COUNT(*) FROM students WHERE company_id = :company_id AND deleted_at IS NULL',
            ['company_id' => $companyId]
        )->fetchColumn();
    }
}
