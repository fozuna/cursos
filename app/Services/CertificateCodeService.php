<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CertificateRepository;
use InvalidArgumentException;

final class CertificateCodeService
{
    public function __construct(
        private readonly CertificateRepository $certificateRepository = new CertificateRepository()
    ) {
    }

    public function nextAvailableCode(?int $year = null, string $prefix = 'CERT'): string
    {
        $year ??= (int) date('Y');
        $sequence = $this->certificateRepository->nextSequenceForYear($year, $prefix);

        return $this->format($prefix, $year, $sequence);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    public function prepareRows(array $rows): array
    {
        $prepared = [];
        $reservedCodes = [];
        $yearSequences = [];

        foreach ($rows as $row) {
            $code = strtoupper(trim((string) ($row['certificate_code'] ?? '')));
            $prefix = strtoupper(trim((string) ($row['certificate_prefix'] ?? 'CERT')));

            if ($code === '') {
                $year = $this->detectYear((string) ($row['completion_date'] ?? ''));
                $sequenceKey = $prefix . '-' . $year;
                $yearSequences[$sequenceKey] ??= $this->certificateRepository->nextSequenceForYear($year, $prefix);

                do {
                    $generatedCode = $this->format($prefix, $year, $yearSequences[$sequenceKey]);
                    $yearSequences[$sequenceKey]++;
                } while (isset($reservedCodes[$generatedCode]) || $this->certificateRepository->existsByCode($generatedCode));

                $code = $generatedCode;
            }

            if (isset($reservedCodes[$code])) {
                throw new InvalidArgumentException(sprintf(
                    'O codigo do certificado "%s" esta duplicado no lote enviado. Utilize codigos unicos.',
                    $code
                ));
            }

            if ($this->certificateRepository->existsByCode($code)) {
                throw new InvalidArgumentException(sprintf(
                    'O codigo do certificado "%s" ja foi utilizado. Informe outro codigo ou deixe o campo em branco para gerar automaticamente.',
                    $code
                ));
            }

            $reservedCodes[$code] = true;
            $row['certificate_code'] = $code;
            $prepared[] = $row;
        }

        return $prepared;
    }

    private function detectYear(string $completionDate): int
    {
        if (preg_match('/^(\d{4})-\d{2}-\d{2}$/', $completionDate, $matches) === 1) {
            return (int) $matches[1];
        }

        return (int) date('Y');
    }

    private function format(string $prefix, int $year, int $sequence): string
    {
        return sprintf('%s-%d-%04d', $prefix, $year, $sequence);
    }
}
