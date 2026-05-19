<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Core\Database;
use App\Repositories\CertificateIssueRegistryRepository;
use App\Services\CertificateIssueIdentityService;
use App\Services\CertificateReissueGuardService;
use PHPUnit\Framework\TestCase;

final class CertificateReissueGuardServiceTest extends TestCase
{
    public function testIntegrationBlocksPreviouslyGeneratedCertificateIdentifier(): void
    {
        $pdo = Database::connection();
        $identityService = new CertificateIssueIdentityService();
        $registry = new CertificateIssueRegistryRepository();
        $service = new CertificateReissueGuardService($identityService, $registry);

        $row = [
            'student_id' => 501,
            'full_name' => 'Aluno Reemitido',
            'course_name' => 'Curso de Reemissao',
            'completion_date' => '2026-05-15',
            'workload_hours' => 12,
            'document_number' => '52998224725',
            'certificate_code' => 'CERT-INTEGRATION-REISSUE',
        ];

        $identifier = $identityService->buildIdentifier(1, $row);

        try {
            $statement = $pdo->prepare(
                'INSERT INTO certificate_issue_registry (
                    company_id, batch_id, certificate_id, student_id, issue_identifier, certificate_code,
                    full_name_snapshot, course_name, completion_date, workload_hours, requested_by,
                    requested_ip, emission_status, blocked_reason, payload_json, issued_at
                 ) VALUES (
                    1, NULL, NULL, NULL, :issue_identifier, :certificate_code,
                    :full_name_snapshot, :course_name, :completion_date, :workload_hours, :requested_by,
                    NULL, "generated", NULL, NULL, NOW()
                 )'
            );
            $statement->execute([
                'issue_identifier' => $identifier,
                'certificate_code' => 'CERT-INTEGRATION-REISSUE',
                'full_name_snapshot' => 'Aluno Reemitido',
                'course_name' => 'Curso de Reemissao',
                'completion_date' => '2026-05-15',
                'workload_hours' => 12,
                'requested_by' => 'Teste Integracao',
            ]);

            $result = $service->filterAlreadyIssued(1, [$row]);

            self::assertCount(0, $result['allowed_rows']);
            self::assertCount(1, $result['blocked_rows']);
        } finally {
            $cleanup = $pdo->prepare('DELETE FROM certificate_issue_registry WHERE issue_identifier = :issue_identifier');
            $cleanup->execute(['issue_identifier' => $identifier]);
        }
    }
}
