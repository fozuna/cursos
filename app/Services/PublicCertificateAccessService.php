<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PublicCertificateAccessLogRepository;
use App\Repositories\PublicCertificateAccessTokenRepository;
use App\Repositories\PublicCertificateRateLimitRepository;
use App\Support\Logger;
use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;

final class PublicCertificateAccessService
{
    private const MAX_INVALID_ATTEMPTS = 5;
    private const ATTEMPT_WINDOW_MINUTES = 15;
    private const TOKEN_TTL_DAYS = 7;
    private const SESSION_GRANT_MINUTES = 30;

    public function __construct(
        private readonly PublicCertificateAccessTokenRepository $tokenRepository = new PublicCertificateAccessTokenRepository(),
        private readonly PublicCertificateRateLimitRepository $rateLimitRepository = new PublicCertificateRateLimitRepository(),
        private readonly PublicCertificateAccessLogRepository $logRepository = new PublicCertificateAccessLogRepository()
    ) {
    }

    public function issueShareLink(int $certificateId, int $companyId): array
    {
        $certificate = $this->tokenRepository->findCertificateForSharing($certificateId, $companyId);

        if ($certificate === null) {
            throw new InvalidArgumentException('O certificado informado nao esta disponivel para compartilhamento publico.');
        }

        $expiresAt = (new DateTimeImmutable())->add(new DateInterval('P' . self::TOKEN_TTL_DAYS . 'D'));
        $plainToken = bin2hex(random_bytes(24));
        $tokenHash = hash('sha256', $plainToken);

        $this->tokenRepository->revokeActiveByCertificate($certificateId);
        $tokenId = $this->tokenRepository->create([
            'company_id' => (int) $certificate['company_id'],
            'student_id' => (int) $certificate['student_id'],
            'certificate_id' => (int) $certificate['id'],
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);

        $link = url('/certificados-publicos/acesso/' . $plainToken);

        $this->recordLog([
            'company_id' => (int) $certificate['company_id'],
            'student_id' => (int) $certificate['student_id'],
            'certificate_id' => (int) $certificate['id'],
            'access_token_id' => $tokenId,
            'ip_address' => '0.0.0.0',
            'user_agent' => 'admin-panel',
            'action' => 'token_issued',
            'status' => 'info',
            'message' => 'Link temporario de acesso publico gerado para certificado.',
        ]);

        return [
            'link' => $link,
            'expires_at' => $expiresAt->format('d/m/Y H:i'),
            'student_name' => (string) $certificate['full_name'],
            'course_name' => (string) $certificate['course_name'],
            'certificate_code' => (string) $certificate['certificate_code'],
        ];
    }

    public function resolveToken(string $plainToken): ?array
    {
        if (!$this->looksLikeToken($plainToken)) {
            return null;
        }

        return $this->tokenRepository->findValidByTokenHash(hash('sha256', $plainToken));
    }

    public function ensureSecureConnection(bool $isSecure): void
    {
        if (config('app.env') === 'local') {
            return;
        }

        if (!$isSecure) {
            throw new InvalidArgumentException('Acesso indisponivel fora de conexao HTTPS segura.');
        }
    }

    public function validatePhoneLookup(string $plainToken, string $phone, string $ipAddress, ?string $userAgent): array
    {
        $this->assertIpAllowed($ipAddress, $userAgent);

        $token = $this->resolveToken($plainToken);

        if ($token === null) {
            throw new InvalidArgumentException('Este link de acesso esta expirado ou nao e mais valido.');
        }

        $normalizedPhone = $this->normalizeBrazilianMobilePhone($phone);
        $studentPhone = preg_replace('/\D+/', '', (string) ($token['phone'] ?? '')) ?: '';

        if ($studentPhone === '' || $normalizedPhone !== $studentPhone) {
            $this->registerInvalidAttempt($ipAddress, $userAgent, $token);

            throw new InvalidArgumentException('Nao foi possivel validar os dados informados. Revise o telefone e tente novamente.');
        }

        $this->rateLimitRepository->clearByIp($ipAddress);
        $this->tokenRepository->touchLastUsed((int) $token['id']);
        $this->grantSessionAccess($plainToken, (int) $token['certificate_id']);

        $this->recordLog([
            'company_id' => (int) $token['company_id'],
            'student_id' => (int) $token['student_id'],
            'certificate_id' => (int) $token['certificate_id'],
            'access_token_id' => (int) $token['id'],
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'action' => 'search_success',
            'status' => 'success',
            'message' => 'Telefone validado com sucesso para acesso ao certificado.',
        ]);

        return $token;
    }

    public function authorizeGrantedAccess(string $plainToken): ?array
    {
        $token = $this->resolveToken($plainToken);

        if ($token === null) {
            return null;
        }

        $sessionKey = hash('sha256', $plainToken);
        $grant = $_SESSION['public_certificate_access'][$sessionKey] ?? null;

        if (!is_array($grant)) {
            return null;
        }

        if ((int) ($grant['certificate_id'] ?? 0) !== (int) $token['certificate_id']) {
            return null;
        }

        if ((int) ($grant['expires_at'] ?? 0) < time()) {
            unset($_SESSION['public_certificate_access'][$sessionKey]);

            return null;
        }

        return $token;
    }

    public function recordPreview(array $token, string $ipAddress, ?string $userAgent): void
    {
        $this->recordLog([
            'company_id' => (int) $token['company_id'],
            'student_id' => (int) $token['student_id'],
            'certificate_id' => (int) $token['certificate_id'],
            'access_token_id' => (int) $token['id'],
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'action' => 'preview',
            'status' => 'success',
            'message' => 'Pre-visualizacao do certificado realizada.',
        ]);
    }

    public function recordDownload(array $token, string $ipAddress, ?string $userAgent): void
    {
        $this->recordLog([
            'company_id' => (int) $token['company_id'],
            'student_id' => (int) $token['student_id'],
            'certificate_id' => (int) $token['certificate_id'],
            'access_token_id' => (int) $token['id'],
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'action' => 'download',
            'status' => 'success',
            'message' => 'Download publico do certificado realizado.',
        ]);
    }

    public function formatPhoneForDisplay(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '';

        if (strlen($digits) !== 11) {
            return $phone;
        }

        return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 5), substr($digits, 7, 4));
    }

    private function normalizeBrazilianMobilePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '';

        if (strlen($digits) !== 11) {
            throw new InvalidArgumentException('Informe um celular no formato (XX) 9XXXX-XXXX.');
        }

        $ddd = (int) substr($digits, 0, 2);

        if ($ddd < 11 || $ddd > 99 || $digits[2] !== '9') {
            throw new InvalidArgumentException('Informe um celular no formato (XX) 9XXXX-XXXX.');
        }

        return $digits;
    }

    private function assertIpAllowed(string $ipAddress, ?string $userAgent): void
    {
        $rateLimit = $this->rateLimitRepository->findByIp($ipAddress);

        if ($rateLimit === null) {
            return;
        }

        $blockedUntil = (string) ($rateLimit['blocked_until'] ?? '');

        if ($blockedUntil !== '' && strtotime($blockedUntil) !== false && strtotime($blockedUntil) > time()) {
            $this->recordLog([
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'action' => 'rate_limited',
                'status' => 'blocked',
                'message' => 'Acesso temporariamente bloqueado por excesso de tentativas invalidas.',
            ]);

            throw new InvalidArgumentException('Acesso temporariamente indisponivel. Aguarde alguns minutos antes de tentar novamente.');
        }
    }

    private function registerInvalidAttempt(string $ipAddress, ?string $userAgent, array $token): void
    {
        $rateLimit = $this->rateLimitRepository->findByIp($ipAddress);
        $now = new DateTimeImmutable();
        $windowStart = $now->sub(new DateInterval('PT' . self::ATTEMPT_WINDOW_MINUTES . 'M'));
        $failedAttempts = 1;
        $firstFailedAt = $now->format('Y-m-d H:i:s');
        $blockedUntil = null;

        if ($rateLimit !== null) {
            $existingFirstFailedAt = (string) ($rateLimit['first_failed_at'] ?? '');

            if ($existingFirstFailedAt !== '' && strtotime($existingFirstFailedAt) !== false && strtotime($existingFirstFailedAt) >= $windowStart->getTimestamp()) {
                $failedAttempts = (int) $rateLimit['failed_attempts'] + 1;
                $firstFailedAt = date('Y-m-d H:i:s', strtotime($existingFirstFailedAt));
            }
        }

        if ($failedAttempts >= self::MAX_INVALID_ATTEMPTS) {
            $blockedUntil = $now->add(new DateInterval('PT' . self::ATTEMPT_WINDOW_MINUTES . 'M'))->format('Y-m-d H:i:s');
        }

        if ($rateLimit === null) {
            $this->rateLimitRepository->create($ipAddress, $failedAttempts, $firstFailedAt, $blockedUntil);
        } else {
            $this->rateLimitRepository->update((int) $rateLimit['id'], $failedAttempts, $firstFailedAt, $blockedUntil);
        }

        $this->recordLog([
            'company_id' => (int) $token['company_id'],
            'student_id' => (int) $token['student_id'],
            'certificate_id' => (int) $token['certificate_id'],
            'access_token_id' => (int) $token['id'],
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'action' => 'search_failed',
            'status' => $blockedUntil !== null ? 'blocked' : 'denied',
            'message' => $blockedUntil !== null
                ? 'Telefone informado invalido; IP bloqueado temporariamente.'
                : 'Telefone informado invalido para o token de acesso publico.',
        ]);
    }

    private function grantSessionAccess(string $plainToken, int $certificateId): void
    {
        if (!isset($_SESSION['public_certificate_access'])) {
            $_SESSION['public_certificate_access'] = [];
        }

        $_SESSION['public_certificate_access'][hash('sha256', $plainToken)] = [
            'certificate_id' => $certificateId,
            'expires_at' => time() + (self::SESSION_GRANT_MINUTES * 60),
        ];
    }

    private function recordLog(array $data): void
    {
        $this->logRepository->create($data);

        Logger::info('public_certificate_access.' . $data['action'], $data);
    }

    private function looksLikeToken(string $plainToken): bool
    {
        return (bool) preg_match('/^[a-f0-9]{48}$/', $plainToken);
    }
}
