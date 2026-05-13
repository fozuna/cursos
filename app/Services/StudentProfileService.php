<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\StudentRepository;
use InvalidArgumentException;

final class StudentProfileService
{
    public function __construct(
        private readonly StudentRepository $studentRepository = new StudentRepository()
    ) {
    }

    public function validateAndNormalize(array $payload, int $companyId, ?int $ignoreId = null): array
    {
        $fullName = trim((string) ($payload['full_name'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $phone = $this->normalizePhone((string) ($payload['phone'] ?? ''));
        $cpf = $this->normalizeCpf((string) ($payload['cpf'] ?? ''));

        if (mb_strlen($fullName) < 3 || mb_strlen($fullName) > 100) {
            throw new InvalidArgumentException('O nome completo deve ter entre 3 e 100 caracteres.');
        }

        if ($email !== '') {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Informe um e-mail valido.');
            }

            if ($this->studentRepository->existsByEmail($companyId, $email, $ignoreId)) {
                throw new InvalidArgumentException('Ja existe um aluno cadastrado com este e-mail.');
            }
        } else {
            $email = null;
        }

        if ($phone !== null && !$this->isValidDdd($phone)) {
            throw new InvalidArgumentException('Informe um telefone com DDD valido.');
        }

        if ($cpf !== null) {
            if (!$this->isValidCpf($cpf)) {
                throw new InvalidArgumentException('Informe um CPF valido.');
            }

            if ($this->studentRepository->existsByCpf($companyId, $cpf, $ignoreId)) {
                throw new InvalidArgumentException('Ja existe um aluno cadastrado com este CPF.');
            }
        }

        return [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'cpf' => $cpf,
            'document_number' => $cpf,
        ];
    }

    private function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '';

        if ($digits === '') {
            return null;
        }

        if (!in_array(strlen($digits), [10, 11], true)) {
            throw new InvalidArgumentException('O telefone deve conter DDD e numero valido.');
        }

        if (strlen($digits) === 10) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 4), substr($digits, 6, 4));
        }

        return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 5), substr($digits, 7, 4));
    }

    private function normalizeCpf(string $cpf): ?string
    {
        $digits = preg_replace('/\D+/', '', $cpf) ?: '';

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) !== 11) {
            throw new InvalidArgumentException('O CPF deve conter 11 digitos.');
        }

        return sprintf('%s.%s.%s-%s', substr($digits, 0, 3), substr($digits, 3, 3), substr($digits, 6, 3), substr($digits, 9, 2));
    }

    private function isValidDdd(string $phone): bool
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '';
        $ddd = (int) substr($digits, 0, 2);

        return $ddd >= 11 && $ddd <= 99;
    }

    private function isValidCpf(string $cpf): bool
    {
        $digits = preg_replace('/\D+/', '', $cpf) ?: '';

        if ($digits === '' || preg_match('/^(\d)\1{10}$/', $digits)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;

            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $digits[$i] * (($t + 1) - $i);
            }

            $digit = ((10 * $sum) % 11) % 10;

            if ((int) $digits[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }
}
