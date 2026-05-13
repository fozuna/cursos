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

    public function index(Request $request, array $params = []): never
    {
        unset($params);

        $hash = trim((string) $request->input('hash', ''));
        $certificate = $hash !== '' ? $this->certificateValidationService->validateHash($hash) : null;

        $this->view('validation.index', [
            'useAdminLayout' => true,
            'activeNav' => 'certificates',
            'activeSubNav' => 'validate',
            'hash' => $hash,
            'certificate' => $certificate,
            'searched' => $hash !== '',
        ]);
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
