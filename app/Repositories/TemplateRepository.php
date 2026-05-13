<?php

declare(strict_types=1);

namespace App\Repositories;

final class TemplateRepository extends AbstractRepository
{
    public function allByCompany(int $companyId): array
    {
        return $this->execute(
            'SELECT * FROM certificate_templates WHERE company_id = :company_id ORDER BY is_default DESC, name ASC',
            ['company_id' => $companyId]
        )->fetchAll();
    }

    public function findDefaultByCompany(int $companyId): ?array
    {
        $statement = $this->execute(
            'SELECT * FROM certificate_templates WHERE company_id = :company_id ORDER BY is_default DESC, id ASC LIMIT 1',
            ['company_id' => $companyId]
        );

        return $statement->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->execute(
            'SELECT * FROM certificate_templates WHERE id = :id LIMIT 1',
            ['id' => $id]
        );

        return $statement->fetch() ?: null;
    }
}
