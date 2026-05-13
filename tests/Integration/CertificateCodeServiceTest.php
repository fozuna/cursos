<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Core\Database;
use App\Services\CertificateCodeService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CertificateCodeServiceTest extends TestCase
{
    public function testPrepareRowsGeneratesUniqueSequentialCodesForBlankValues(): void
    {
        $service = new CertificateCodeService();

        $rows = $service->prepareRows([
            [
                'full_name' => 'Aluno Um',
                'course_name' => 'Curso Sequencial',
                'workload_hours' => 10,
                'completion_date' => '2099-01-10',
                'certificate_code' => '',
                'instructor_name' => 'Instrutor',
                'institution_name' => 'Instituicao',
            ],
            [
                'full_name' => 'Aluno Dois',
                'course_name' => 'Curso Sequencial',
                'workload_hours' => 10,
                'completion_date' => '2099-01-10',
                'certificate_code' => '',
                'instructor_name' => 'Instrutor',
                'institution_name' => 'Instituicao',
            ],
        ]);

        self::assertMatchesRegularExpression('/^CERT-2099-\d{4}$/', $rows[0]['certificate_code']);
        self::assertMatchesRegularExpression('/^CERT-2099-\d{4}$/', $rows[1]['certificate_code']);
        self::assertNotSame($rows[0]['certificate_code'], $rows[1]['certificate_code']);
    }

    public function testPrepareRowsRejectsExistingCodeBeforeInsert(): void
    {
        $pdo = Database::connection();
        $code = 'CERT-TEST-DUP-' . substr(str_replace('.', '', (string) microtime(true)), -6);

        $pdo->exec("INSERT INTO students (company_id, full_name) VALUES (1, 'Aluno Duplicado Teste')");
        $studentId = (int) $pdo->lastInsertId();

        $statement = $pdo->prepare(
            'INSERT INTO certificates (
                company_id, student_id, template_id, batch_id, course_name, workload_hours, completion_date,
                certificate_code, instructor_name, institution_name, program_content, validation_hash, validation_url, pdf_path, status, metadata_json
            ) VALUES (
                1, :student_id, 1, NULL, :course_name, 8, :completion_date,
                :certificate_code, :instructor_name, :institution_name, :program_content, :validation_hash, :validation_url, NULL, "generated", NULL
            )'
        );

        $statement->execute([
            'student_id' => $studentId,
            'course_name' => 'Curso Existente',
            'completion_date' => '2026-05-13',
            'certificate_code' => $code,
            'instructor_name' => 'Instrutor Existente',
            'institution_name' => 'Academia Corporativa Premium',
            'program_content' => 'Programa existente',
            'validation_hash' => hash('sha256', $code),
            'validation_url' => 'http://localhost/cursos/public/validar/' . hash('sha256', $code),
        ]);

        $service = new CertificateCodeService();

        try {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage($code);

            $service->prepareRows([
                [
                    'full_name' => 'Aluno Novo',
                    'course_name' => 'Curso Novo',
                    'workload_hours' => 8,
                    'completion_date' => '2026-05-13',
                    'certificate_code' => $code,
                    'instructor_name' => 'Instrutor Novo',
                    'institution_name' => 'Academia Corporativa Premium',
                ],
            ]);
        } finally {
            $cleanup = $pdo->prepare('DELETE FROM certificates WHERE certificate_code = :certificate_code');
            $cleanup->execute(['certificate_code' => $code]);
            $cleanup = $pdo->prepare('DELETE FROM students WHERE id = :id');
            $cleanup->execute(['id' => $studentId]);
        }
    }
}
