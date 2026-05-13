<?php

declare(strict_types=1);

namespace App\Models;

final readonly class Student
{
    public function __construct(
        public int $id,
        public int $companyId,
        public string $fullName,
        public ?string $email,
        public ?string $documentNumber
    ) {
    }
}
