<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CertificateRepository;

final class CertificateValidationService
{
    public function __construct(
        private readonly CertificateRepository $certificateRepository = new CertificateRepository()
    ) {
    }

    public function validateHash(string $hash): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $hash)) {
            return null;
        }

        return $this->certificateRepository->findByHash($hash);
    }
}
