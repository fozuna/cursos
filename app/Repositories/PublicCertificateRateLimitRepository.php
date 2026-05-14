<?php

declare(strict_types=1);

namespace App\Repositories;

class PublicCertificateRateLimitRepository extends AbstractRepository
{
    public function findByIp(string $ipAddress): ?array
    {
        $statement = $this->execute(
            'SELECT * FROM public_certificate_rate_limits WHERE ip_address = :ip_address LIMIT 1',
            ['ip_address' => $ipAddress]
        );

        return $statement->fetch() ?: null;
    }

    public function create(string $ipAddress, int $failedAttempts, ?string $firstFailedAt, ?string $blockedUntil): void
    {
        $this->execute(
            'INSERT INTO public_certificate_rate_limits (
                ip_address, failed_attempts, first_failed_at, blocked_until
             ) VALUES (
                :ip_address, :failed_attempts, :first_failed_at, :blocked_until
             )',
            [
                'ip_address' => $ipAddress,
                'failed_attempts' => $failedAttempts,
                'first_failed_at' => $firstFailedAt,
                'blocked_until' => $blockedUntil,
            ]
        );
    }

    public function update(int $id, int $failedAttempts, ?string $firstFailedAt, ?string $blockedUntil): void
    {
        $this->execute(
            'UPDATE public_certificate_rate_limits
             SET failed_attempts = :failed_attempts,
                 first_failed_at = :first_failed_at,
                 blocked_until = :blocked_until,
                 updated_at = NOW()
             WHERE id = :id',
            [
                'id' => $id,
                'failed_attempts' => $failedAttempts,
                'first_failed_at' => $firstFailedAt,
                'blocked_until' => $blockedUntil,
            ]
        );
    }

    public function clearByIp(string $ipAddress): void
    {
        $this->execute(
            'DELETE FROM public_certificate_rate_limits WHERE ip_address = :ip_address',
            ['ip_address' => $ipAddress]
        );
    }
}
