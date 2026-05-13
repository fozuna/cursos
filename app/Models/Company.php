<?php

declare(strict_types=1);

namespace App\Models;

final readonly class Company
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public string $primaryColor,
        public string $secondaryColor,
        public string $themeMode
    ) {
    }
}
