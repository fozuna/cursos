<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CompanyRepository;
use App\Services\PublicCertificateAccessService;
use App\Support\Csrf;
use InvalidArgumentException;

final class PublicCertificateAccessController extends Controller
{
    public function __construct(
        private readonly PublicCertificateAccessService $publicCertificateAccessService = new PublicCertificateAccessService(),
        private readonly CompanyRepository $companyRepository = new CompanyRepository()
    ) {
    }

    public function share(Request $request, array $params = []): never
    {
        unset($request);

        $company = $this->companyRepository->findDefault();

        if ($company === null) {
            throw new InvalidArgumentException('Empresa padrao nao encontrada.');
        }

        $certificateId = (int) ($params['id'] ?? 0);
        $share = $this->publicCertificateAccessService->issueShareLink($certificateId, (int) $company['id']);

        $this->view('certificates.share', [
            'useAdminLayout' => true,
            'activeNav' => 'certificates',
            'activeSubNav' => 'list',
            'company' => $company,
            'share' => $share,
        ]);
    }

    public function show(Request $request, array $params = []): never
    {
        $token = (string) ($params['token'] ?? '');
        $errorMessage = null;
        $certificate = null;

        try {
            $this->publicCertificateAccessService->ensureSecureConnection($request->isSecure());
            $tokenData = $this->publicCertificateAccessService->resolveToken($token);

            if ($tokenData === null) {
                throw new InvalidArgumentException('Este link de acesso esta expirado ou nao e mais valido.');
            }

            $certificate = $this->publicCertificateAccessService->authorizeGrantedAccess($token);
        } catch (InvalidArgumentException $exception) {
            $errorMessage = $exception->getMessage();
            $tokenData = null;
        }

        $this->view('public_certificates.access', [
            'token' => $token,
            'tokenData' => $tokenData ?? null,
            'certificate' => $certificate,
            'errorMessage' => $errorMessage,
            'csrfToken' => Csrf::token('public_certificate_access'),
            'formPhone' => '',
        ]);
    }

    public function authenticate(Request $request, array $params = []): never
    {
        $token = (string) ($params['token'] ?? '');
        $formPhone = (string) $request->input('phone', '');
        $errorMessage = null;
        $certificate = null;

        try {
            $this->publicCertificateAccessService->ensureSecureConnection($request->isSecure());

            if (!Csrf::validate((string) $request->input('_csrf_token', ''), 'public_certificate_access')) {
                throw new InvalidArgumentException('Nao foi possivel validar a solicitacao. Atualize a pagina e tente novamente.');
            }

            $certificate = $this->publicCertificateAccessService->validatePhoneLookup(
                $token,
                $formPhone,
                $request->ip(),
                $request->userAgent()
            );
            $tokenData = $certificate;
        } catch (InvalidArgumentException $exception) {
            $errorMessage = $exception->getMessage();
            $tokenData = $this->publicCertificateAccessService->resolveToken($token);
        }

        $this->view('public_certificates.access', [
            'token' => $token,
            'tokenData' => $tokenData ?? null,
            'certificate' => $certificate,
            'errorMessage' => $errorMessage,
            'csrfToken' => Csrf::token('public_certificate_access'),
            'formPhone' => $formPhone,
        ]);
    }

    public function preview(Request $request, array $params = []): never
    {
        $token = (string) ($params['token'] ?? '');
        $this->publicCertificateAccessService->ensureSecureConnection($request->isSecure());
        $tokenData = $this->publicCertificateAccessService->authorizeGrantedAccess($token);

        if ($tokenData === null) {
            Response::html('Acesso nao autorizado.', 403);
        }

        $absolutePath = public_path((string) $tokenData['pdf_path']);
        $this->publicCertificateAccessService->recordPreview($tokenData, $request->ip(), $request->userAgent());

        Response::inlinePdf($absolutePath, basename((string) $tokenData['pdf_path']));
    }

    public function download(Request $request, array $params = []): never
    {
        $token = (string) ($params['token'] ?? '');
        $this->publicCertificateAccessService->ensureSecureConnection($request->isSecure());
        $tokenData = $this->publicCertificateAccessService->authorizeGrantedAccess($token);

        if ($tokenData === null) {
            Response::html('Acesso nao autorizado.', 403);
        }

        $absolutePath = public_path((string) $tokenData['pdf_path']);
        $this->publicCertificateAccessService->recordDownload($tokenData, $request->ip(), $request->userAgent());

        Response::download($absolutePath, basename((string) $tokenData['pdf_path']));
    }
}
