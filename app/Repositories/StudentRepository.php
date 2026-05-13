<?php

declare(strict_types=1);

namespace App\Repositories;

final class StudentRepository extends AbstractRepository
{
    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO students (company_id, full_name, email, document_number)
             VALUES (:company_id, :full_name, :email, :document_number)',
            [
                'company_id' => $data['company_id'],
                'full_name' => $data['full_name'],
                'email' => $data['email'] ?? null,
                'document_number' => $data['document_number'] ?? null,
            ]
        );

        return (int) $this->db->lastInsertId();
    }
}
