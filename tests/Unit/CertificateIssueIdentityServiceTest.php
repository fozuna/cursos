<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CertificateIssueIdentityService;
use PHPUnit\Framework\TestCase;

final class CertificateIssueIdentityServiceTest extends TestCase
{
    public function testBuildIdentifierRemainsStableForSameLogicalCertificate(): void
    {
        $service = new CertificateIssueIdentityService();

        $first = $service->buildIdentifier(1, [
            'student_id' => 10,
            'full_name' => 'Maria Fernanda Costa',
            'course_name' => 'Excel Avancado',
            'completion_date' => '2026-05-15',
            'workload_hours' => 24,
            'document_number' => '529.982.247-25',
        ]);

        $second = $service->buildIdentifier(1, [
            'student_id' => 10,
            'full_name' => 'maria fernanda costa',
            'course_name' => 'excel avancado',
            'completion_date' => '2026-05-15',
            'workload_hours' => 24,
            'document_number' => '52998224725',
        ]);

        self::assertSame($first, $second);
    }

    public function testBuildIdentifierChangesWhenCompletionDateDiffers(): void
    {
        $service = new CertificateIssueIdentityService();

        $first = $service->buildIdentifier(1, [
            'student_id' => 10,
            'full_name' => 'Maria Fernanda Costa',
            'course_name' => 'Excel Avancado',
            'completion_date' => '2026-05-15',
            'workload_hours' => 24,
            'document_number' => '52998224725',
        ]);

        $second = $service->buildIdentifier(1, [
            'student_id' => 10,
            'full_name' => 'Maria Fernanda Costa',
            'course_name' => 'Excel Avancado',
            'completion_date' => '2026-06-15',
            'workload_hours' => 24,
            'document_number' => '52998224725',
        ]);

        self::assertNotSame($first, $second);
    }
}
