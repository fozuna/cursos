<?php

declare(strict_types=1);

namespace App\Models;

final readonly class GenerationBatch
{
    public function __construct(
        public int $id,
        public int $companyId,
        public string $name,
        public string $importSource,
        public ?string $sourceFile,
        public int $totalItems,
        public int $processedItems,
        public string $status
    ) {
    }
}
