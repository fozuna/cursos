<?php

declare(strict_types=1);

namespace App\Repositories;

final class CompanyRepository extends AbstractRepository
{
    public function findDefault(): ?array
    {
        $statement = $this->execute(
            'SELECT * FROM companies WHERE id = :id LIMIT 1',
            ['id' => config('app.default_company_id')]
        );

        return $statement->fetch() ?: null;
    }
}
