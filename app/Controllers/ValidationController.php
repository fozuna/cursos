<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Services\CertificateValidationService;

final class ValidationController extends Controller
{
    public function __construct(
        private readonly CertificateValidationService $certificateValidationService = new CertificateValidationService()
    ) {
    }

    public function show(Request $request, array $params = []): never
    {
        unset($request);

        $hash = (string) ($params['hash'] ?? '');
        $certificate = $this->certificateValidationService->validateHash($hash);

        $this->view('validation.show', [
            'hash' => $hash,
            'certificate' => $certificate,
        ]);
    }
}
