<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\CertificateIssueRegistryRepository;
use App\Services\CertificateIssueIdentityService;
use App\Services\CertificateReissueGuardService;
use PHPUnit\Framework\TestCase;

final class CertificateReissueGuardServiceTest extends TestCase
{
    public function testBlocksEntireBatchWhenAllCertificatesWerePreviouslyIssued(): void
    {
        $identityService = new CertificateIssueIdentityService();
        $rows = [
            $this->buildRow(10, 'Maria Costa', 'Excel', '2026-05-15'),
            $this->buildRow(11, 'Joao Lima', 'Excel', '2026-05-15'),
        ];

        $registry = new class ($identityService, $rows) extends CertificateIssueRegistryRepository {
            private array $existing;

            public function __construct(CertificateIssueIdentityService $identityService, array $rows)
            {
                $this->existing = [];

                foreach ($rows as $row) {
                    $identifier = $identityService->buildIdentifier(1, $row);
                    $this->existing[$identifier] = [
                        'issue_identifier' => $identifier,
                        'certificate_code' => 'CERT-OLD-' . $row['student_id'],
                        'issued_at' => '2026-05-20 10:00:00',
                    ];
                }
            }

            public function findGeneratedByIdentifiers(int $companyId, array $issueIdentifiers): array
            {
                return array_intersect_key($this->existing, array_flip($issueIdentifiers));
            }
        };

        $service = new CertificateReissueGuardService($identityService, $registry);
        $result = $service->filterAlreadyIssued(1, $rows);

        self::assertCount(0, $result['allowed_rows']);
        self::assertCount(2, $result['blocked_rows']);
    }

    public function testBlocksOnlyRepeatedCertificatesInMixedBatch(): void
    {
        $identityService = new CertificateIssueIdentityService();
        $rows = [
            $this->buildRow(10, 'Maria Costa', 'Excel', '2026-05-15'),
            $this->buildRow(11, 'Joao Lima', 'Power BI', '2026-05-16'),
        ];
        $blockedIdentifier = $identityService->buildIdentifier(1, $rows[0]);

        $registry = new class ($blockedIdentifier) extends CertificateIssueRegistryRepository {
            public function __construct(private readonly string $blockedIdentifier)
            {
            }

            public function findGeneratedByIdentifiers(int $companyId, array $issueIdentifiers): array
            {
                if (!in_array($this->blockedIdentifier, $issueIdentifiers, true)) {
                    return [];
                }

                return [
                    $this->blockedIdentifier => [
                        'issue_identifier' => $this->blockedIdentifier,
                        'certificate_code' => 'CERT-2026-0001',
                        'issued_at' => '2026-05-20 10:00:00',
                    ],
                ];
            }
        };

        $service = new CertificateReissueGuardService($identityService, $registry);
        $result = $service->filterAlreadyIssued(1, $rows);

        self::assertCount(1, $result['allowed_rows']);
        self::assertCount(1, $result['blocked_rows']);
        self::assertSame('Joao Lima', $result['allowed_rows'][0]['full_name']);
    }

    public function testAllowsEntirelyNewBatch(): void
    {
        $rows = [
            $this->buildRow(10, 'Maria Costa', 'Excel', '2026-05-15'),
            $this->buildRow(11, 'Joao Lima', 'Power BI', '2026-05-16'),
        ];

        $registry = new class () extends CertificateIssueRegistryRepository {
            public function __construct()
            {
            }

            public function findGeneratedByIdentifiers(int $companyId, array $issueIdentifiers): array
            {
                return [];
            }
        };

        $service = new CertificateReissueGuardService(new CertificateIssueIdentityService(), $registry);
        $result = $service->filterAlreadyIssued(1, $rows);

        self::assertCount(2, $result['allowed_rows']);
        self::assertCount(0, $result['blocked_rows']);
    }

    private function buildRow(int $studentId, string $fullName, string $courseName, string $completionDate): array
    {
        return [
            'student_id' => $studentId,
            'full_name' => $fullName,
            'course_name' => $courseName,
            'completion_date' => $completionDate,
            'workload_hours' => 24,
            'document_number' => '52998224725',
            'certificate_code' => 'CERT-TMP-' . $studentId,
        ];
    }
}
