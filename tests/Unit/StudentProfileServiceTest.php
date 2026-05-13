<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\StudentRepository;
use App\Services\StudentProfileService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class StudentProfileServiceTest extends TestCase
{
    public function testValidateAndNormalizeFormatsPhoneAndCpf(): void
    {
        $repository = new class () extends StudentRepository {
            public function __construct()
            {
            }

            public function existsByEmail(int $companyId, string $email, ?int $ignoreId = null): bool
            {
                return false;
            }

            public function existsByCpf(int $companyId, string $cpf, ?int $ignoreId = null): bool
            {
                return false;
            }
        };

        $service = new StudentProfileService($repository);
        $result = $service->validateAndNormalize([
            'full_name' => 'Maria Fernanda Costa',
            'email' => 'maria@example.com',
            'phone' => '11987654321',
            'cpf' => '52998224725',
        ], 1);

        self::assertSame('(11) 98765-4321', $result['phone']);
        self::assertSame('529.982.247-25', $result['cpf']);
        self::assertSame('529.982.247-25', $result['document_number']);
    }

    public function testValidateAndNormalizeRejectsDuplicateEmail(): void
    {
        $repository = new class () extends StudentRepository {
            public function __construct()
            {
            }

            public function existsByEmail(int $companyId, string $email, ?int $ignoreId = null): bool
            {
                return true;
            }

            public function existsByCpf(int $companyId, string $cpf, ?int $ignoreId = null): bool
            {
                return false;
            }
        };

        $service = new StudentProfileService($repository);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ja existe um aluno cadastrado com este e-mail.');

        $service->validateAndNormalize([
            'full_name' => 'Maria Fernanda Costa',
            'email' => 'maria@example.com',
        ], 1);
    }

    public function testValidateAndNormalizeRejectsInvalidCpf(): void
    {
        $repository = new class () extends StudentRepository {
            public function __construct()
            {
            }

            public function existsByEmail(int $companyId, string $email, ?int $ignoreId = null): bool
            {
                return false;
            }

            public function existsByCpf(int $companyId, string $cpf, ?int $ignoreId = null): bool
            {
                return false;
            }
        };

        $service = new StudentProfileService($repository);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Informe um CPF valido.');

        $service->validateAndNormalize([
            'full_name' => 'Maria Fernanda Costa',
            'cpf' => '11111111111',
        ], 1);
    }
}
