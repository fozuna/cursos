<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CertificateIssueRegistryRepository;

final class CertificateReissueGuardService
{
    public function __construct(
        private readonly CertificateIssueIdentityService $identityService = new CertificateIssueIdentityService(),
        private readonly CertificateIssueRegistryRepository $registryRepository = new CertificateIssueRegistryRepository()
    ) {
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array{
     *   allowed_rows: array<int, array<string, mixed>>,
     *   blocked_rows: array<int, array<string, mixed>>,
     *   identifiers: array<int, string>,
     *   allowed_identifiers: array<int, string>,
     *   blocked_identifiers: array<int, string>
     * }
     */
    public function filterAlreadyIssued(int $companyId, array $rows): array
    {
        $identifiers = [];

        foreach ($rows as $index => $row) {
            $identifiers[$index] = $this->identityService->buildIdentifier($companyId, $row);
        }

        $existing = $this->registryRepository->findGeneratedByIdentifiers($companyId, array_values($identifiers));
        $allowedRows = [];
        $blockedRows = [];
        $allowedIdentifiers = [];
        $blockedIdentifiers = [];

        foreach ($rows as $index => $row) {
            $identifier = $identifiers[$index];

            if (isset($existing[$identifier])) {
                $blockedRows[] = [
                    'row' => $row,
                    'identifier' => $identifier,
                    'existing' => $existing[$identifier],
                    'reason' => sprintf(
                        'Certificado ja emitido anteriormente em %s%s.',
                        format_date_br(substr((string) $existing[$identifier]['issued_at'], 0, 10)),
                        !empty($existing[$identifier]['certificate_code']) ? ' sob o codigo ' . (string) $existing[$identifier]['certificate_code'] : ''
                    ),
                ];
                $blockedIdentifiers[] = $identifier;
                continue;
            }

            $allowedRows[] = $row;
            $allowedIdentifiers[] = $identifier;
        }

        return [
            'allowed_rows' => $allowedRows,
            'blocked_rows' => $blockedRows,
            'identifiers' => array_values($identifiers),
            'allowed_identifiers' => $allowedIdentifiers,
            'blocked_identifiers' => $blockedIdentifiers,
        ];
    }
}
