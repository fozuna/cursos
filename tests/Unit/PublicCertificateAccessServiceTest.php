<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\PublicCertificateAccessLogRepository;
use App\Repositories\PublicCertificateAccessTokenRepository;
use App\Repositories\PublicCertificateRateLimitRepository;
use App\Services\PublicCertificateAccessService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PublicCertificateAccessServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];
    }

    public function testValidatePhoneLookupAuthorizesSessionWhenPhoneMatches(): void
    {
        $token = str_repeat('a', 48);
        $tokenHash = hash('sha256', $token);
        $tokenRepository = new class ($tokenHash) extends PublicCertificateAccessTokenRepository {
            public array $token;
            public bool $touched = false;

            public function __construct(private readonly string $expectedHash)
            {
                $this->token = [
                    'id' => 10,
                    'company_id' => 1,
                    'student_id' => 2,
                    'certificate_id' => 3,
                    'token_hash' => $expectedHash,
                    'course_name' => 'Curso Seguro',
                    'workload_hours' => 16,
                    'completion_date' => '2026-05-13',
                    'certificate_code' => 'CERT-UNIT-0001',
                    'institution_name' => 'Academia Corporativa Premium',
                    'validation_hash' => hash('sha256', 'unit'),
                    'pdf_path' => 'storage/certificados/unit.pdf',
                    'certificate_status' => 'generated',
                    'full_name' => 'Aluno Unitario',
                    'phone' => '(11) 98765-4321',
                    'company_name' => 'Academia Corporativa Premium',
                    'expires_at' => '2099-01-01 10:00:00',
                ];
            }

            public function findValidByTokenHash(string $tokenHash): ?array
            {
                return $tokenHash === $this->expectedHash ? $this->token : null;
            }

            public function touchLastUsed(int $id): void
            {
                $this->touched = $id === (int) $this->token['id'];
            }
        };
        $rateRepository = new class () extends PublicCertificateRateLimitRepository {
            public ?array $record = null;
            public bool $cleared = false;

            public function __construct()
            {
            }

            public function findByIp(string $ipAddress): ?array
            {
                return $this->record;
            }

            public function clearByIp(string $ipAddress): void
            {
                $this->cleared = true;
                $this->record = null;
            }
        };
        $logRepository = new class () extends PublicCertificateAccessLogRepository {
            public array $entries = [];

            public function __construct()
            {
            }

            public function create(array $data): void
            {
                $this->entries[] = $data;
            }
        };

        $service = new PublicCertificateAccessService($tokenRepository, $rateRepository, $logRepository);
        $result = $service->validatePhoneLookup($token, '(11) 98765-4321', '127.0.0.50', 'PHPUnit');
        $granted = $service->authorizeGrantedAccess($token);

        self::assertSame('Aluno Unitario', $result['full_name']);
        self::assertTrue($tokenRepository->touched);
        self::assertTrue($rateRepository->cleared);
        self::assertNotNull($granted);
        self::assertSame(3, (int) $granted['certificate_id']);
        self::assertCount(1, $logRepository->entries);
        self::assertSame('search_success', $logRepository->entries[0]['action']);
    }

    public function testValidatePhoneLookupBlocksIpAfterFiveInvalidAttempts(): void
    {
        $token = str_repeat('b', 48);
        $tokenHash = hash('sha256', $token);
        $tokenRepository = new class ($tokenHash) extends PublicCertificateAccessTokenRepository {
            public function __construct(private readonly string $expectedHash)
            {
            }

            public function findValidByTokenHash(string $tokenHash): ?array
            {
                if ($tokenHash !== $this->expectedHash) {
                    return null;
                }

                return [
                    'id' => 11,
                    'company_id' => 1,
                    'student_id' => 2,
                    'certificate_id' => 4,
                    'phone' => '(11) 99999-1111',
                    'full_name' => 'Aluno Unitario',
                    'course_name' => 'Curso Seguro',
                    'workload_hours' => 16,
                    'completion_date' => '2026-05-13',
                    'certificate_code' => 'CERT-UNIT-0002',
                    'institution_name' => 'Academia Corporativa Premium',
                    'validation_hash' => hash('sha256', 'unit-2'),
                    'pdf_path' => 'storage/certificados/unit-2.pdf',
                    'certificate_status' => 'generated',
                    'company_name' => 'Academia Corporativa Premium',
                    'expires_at' => '2099-01-01 10:00:00',
                ];
            }
        };
        $rateRepository = new class () extends PublicCertificateRateLimitRepository {
            public ?array $record = null;

            public function __construct()
            {
            }

            public function findByIp(string $ipAddress): ?array
            {
                return $this->record;
            }

            public function create(string $ipAddress, int $failedAttempts, ?string $firstFailedAt, ?string $blockedUntil): void
            {
                $this->record = [
                    'id' => 1,
                    'ip_address' => $ipAddress,
                    'failed_attempts' => $failedAttempts,
                    'first_failed_at' => $firstFailedAt,
                    'blocked_until' => $blockedUntil,
                ];
            }

            public function update(int $id, int $failedAttempts, ?string $firstFailedAt, ?string $blockedUntil): void
            {
                $this->record = [
                    'id' => $id,
                    'ip_address' => '127.0.0.60',
                    'failed_attempts' => $failedAttempts,
                    'first_failed_at' => $firstFailedAt,
                    'blocked_until' => $blockedUntil,
                ];
            }
        };
        $logRepository = new class () extends PublicCertificateAccessLogRepository {
            public array $entries = [];

            public function __construct()
            {
            }

            public function create(array $data): void
            {
                $this->entries[] = $data;
            }
        };

        $service = new PublicCertificateAccessService($tokenRepository, $rateRepository, $logRepository);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            try {
                $service->validatePhoneLookup($token, '(11) 98888-0000', '127.0.0.60', 'PHPUnit');
                self::fail('A validacao deveria falhar com telefone incorreto.');
            } catch (InvalidArgumentException $exception) {
                self::assertStringContainsString('Nao foi possivel validar os dados informados', $exception->getMessage());
            }
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Acesso temporariamente indisponivel');

        $service->validatePhoneLookup($token, '(11) 98888-0000', '127.0.0.60', 'PHPUnit');
    }
}
