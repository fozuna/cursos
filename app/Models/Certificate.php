<?php

declare(strict_types=1);

namespace App\Models;

final readonly class Certificate
{
    public function __construct(
        public int $id,
        public int $companyId,
        public int $studentId,
        public int $templateId,
        public ?int $batchId,
        public string $courseName,
        public int $workloadHours,
        public string $completionDate,
        public string $certificateCode,
        public string $instructorName,
        public string $institutionName,
        public string $validationHash,
        public string $validationUrl,
        public ?string $pdfPath,
        public string $status
    ) {
    }
}
