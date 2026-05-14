<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Core\ApplicationBootstrap;
use App\Core\Database;
use App\Services\PublicCertificateAccessService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PublicCertificateAccessServiceTest extends TestCase
{
    private array $createdCertificateIds = [];
    private array $createdStudentIds = [];
    private array $createdPdfFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        ApplicationBootstrap::initialize();

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $pdo = Database::connection();

        foreach ($this->createdCertificateIds as $certificateId) {
            $statement = $pdo->prepare('DELETE FROM public_certificate_access_logs WHERE certificate_id = :certificate_id');
            $statement->execute(['certificate_id' => $certificateId]);

            $statement = $pdo->prepare('DELETE FROM public_certificate_access_tokens WHERE certificate_id = :certificate_id');
            $statement->execute(['certificate_id' => $certificateId]);

            $statement = $pdo->prepare('DELETE FROM certificates WHERE id = :id');
            $statement->execute(['id' => $certificateId]);
        }

        foreach ($this->createdStudentIds as $studentId) {
            $statement = $pdo->prepare('DELETE FROM students WHERE id = :id');
            $statement->execute(['id' => $studentId]);
        }

        $pdo->exec('DELETE FROM public_certificate_rate_limits');

        foreach ($this->createdPdfFiles as $filePath) {
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }

        $_SESSION = [];

        parent::tearDown();
    }

    public function testIssueShareLinkAndValidatePhoneGrantPublicAccess(): void
    {
        $fixture = $this->createCertificateFixture('(11) 98765-4321');
        $service = new PublicCertificateAccessService();

        $share = $service->issueShareLink($fixture['certificate_id'], 1);

        self::assertStringContainsString('/certificados-publicos/acesso/', $share['link']);
        self::assertSame('Aluno Publico Teste', $share['student_name']);

        $token = basename((string) parse_url($share['link'], PHP_URL_PATH));
        $result = $service->validatePhoneLookup($token, '(11) 98765-4321', '127.0.0.21', 'PHPUnit');
        $granted = $service->authorizeGrantedAccess($token);

        self::assertSame('Aluno Publico Teste', $result['full_name']);
        self::assertSame($fixture['certificate_id'], (int) $result['certificate_id']);
        self::assertNotNull($granted);
        self::assertSame($fixture['certificate_id'], (int) $granted['certificate_id']);
    }

    public function testBlocksIpAfterFiveInvalidAttemptsWithinFifteenMinutes(): void
    {
        $fixture = $this->createCertificateFixture('(11) 99999-1111');
        $service = new PublicCertificateAccessService();
        $share = $service->issueShareLink($fixture['certificate_id'], 1);
        $token = basename((string) parse_url($share['link'], PHP_URL_PATH));
        $ipAddress = '127.0.0.31';

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            try {
                $service->validatePhoneLookup($token, '(11) 98888-0000', $ipAddress, 'PHPUnit');
                self::fail('A validacao deveria falhar para telefone incorreto.');
            } catch (InvalidArgumentException $exception) {
                self::assertStringContainsString('Nao foi possivel validar os dados informados', $exception->getMessage());
            }
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Acesso temporariamente indisponivel');

        $service->validatePhoneLookup($token, '(11) 98888-0000', $ipAddress, 'PHPUnit');
    }

    private function createCertificateFixture(string $phone): array
    {
        $pdo = Database::connection();
        $suffix = substr(str_replace('.', '', (string) microtime(true)), -8);
        $validationHash = hash('sha256', 'public-access-' . $suffix);
        $certificateCode = 'CERT-PUBLIC-' . $suffix;
        $pdfRelativePath = 'storage/certificados/public-access-' . $suffix . '.pdf';
        $pdfAbsolutePath = public_path($pdfRelativePath);

        file_put_contents($pdfAbsolutePath, "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF");
        $this->createdPdfFiles[] = $pdfAbsolutePath;

        $studentStatement = $pdo->prepare(
            'INSERT INTO students (company_id, full_name, email, phone, cpf, document_number)
             VALUES (1, :full_name, :email, :phone, NULL, NULL)'
        );
        $studentStatement->execute([
            'full_name' => 'Aluno Publico Teste',
            'email' => 'publico.' . $suffix . '@example.com',
            'phone' => $phone,
        ]);
        $studentId = (int) $pdo->lastInsertId();
        $this->createdStudentIds[] = $studentId;

        $certificateStatement = $pdo->prepare(
            'INSERT INTO certificates (
                company_id, student_id, template_id, batch_id, course_name, workload_hours, completion_date,
                certificate_code, instructor_name, institution_name, program_content, validation_hash, validation_url, pdf_path, status, metadata_json
             ) VALUES (
                1, :student_id, 1, NULL, :course_name, 24, :completion_date,
                :certificate_code, :instructor_name, :institution_name, :program_content, :validation_hash, :validation_url, :pdf_path, "generated", NULL
             )'
        );
        $certificateStatement->execute([
            'student_id' => $studentId,
            'course_name' => 'Curso Publico Seguro',
            'completion_date' => '2026-05-13',
            'certificate_code' => $certificateCode,
            'instructor_name' => 'Instrutor Publico',
            'institution_name' => 'Academia Corporativa Premium',
            'program_content' => 'Conteudo programatico completo.',
            'validation_hash' => $validationHash,
            'validation_url' => 'http://localhost/cursos/public/validar/' . $validationHash,
            'pdf_path' => $pdfRelativePath,
        ]);
        $certificateId = (int) $pdo->lastInsertId();
        $this->createdCertificateIds[] = $certificateId;

        return [
            'student_id' => $studentId,
            'certificate_id' => $certificateId,
        ];
    }
}
