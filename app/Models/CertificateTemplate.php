<?php

declare(strict_types=1);

namespace App\Models;

final readonly class CertificateTemplate
{
    public function __construct(
        public int $id,
        public int $companyId,
        public string $name,
        public string $slug,
        public string $themeMode,
        public string $backgroundType,
        public array $settings,
        public bool $isDefault
    ) {
    }
}
