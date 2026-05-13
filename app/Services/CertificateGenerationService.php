<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Repositories\BatchRepository;
use App\Repositories\CertificateRepository;
use App\Repositories\HistoryRepository;
use App\Repositories\StudentRepository;
use App\Repositories\TemplateRepository;
use PDOException;
use Throwable;

final class CertificateGenerationService
{
    public function __construct(
        private readonly StudentRepository $studentRepository = new StudentRepository(),
        private readonly CertificateRepository $certificateRepository = new CertificateRepository(),
        private readonly BatchRepository $batchRepository = new BatchRepository(),
        private readonly HistoryRepository $historyRepository = new HistoryRepository(),
        private readonly TemplateRepository $templateRepository = new TemplateRepository(),
        private readonly PdfGeneratorService $pdfGeneratorService = new PdfGeneratorService(),
        private readonly QrCodeService $qrCodeService = new QrCodeService()
    ) {
    }

    public function generate(
        array $rows,
        string $sourceType,
        ?string $sourceFile,
        int $companyId,
        array $programContent,
        ?int $templateId = null
    ): array
    {
        $template = $templateId !== null
            ? $this->templateRepository->findById($templateId)
            : $this->templateRepository->findDefaultByCompany($companyId);

        if ($template === null) {
            throw new \RuntimeException('Template padrao nao encontrado.');
        }

        $batchId = $this->batchRepository->create([
            'company_id' => $companyId,
            'name' => 'Lote ' . date('d/m/Y H:i'),
            'import_source' => $sourceType,
            'source_file' => $sourceFile,
            'total_items' => count($rows),
            'status' => 'processing',
        ]);

        $generated = [];
        $failures = [];
        $processed = 0;

        foreach ($rows as $row) {
            $processed++;
            $certificateId = null;

            try {
                $db = Database::connection();
                $db->beginTransaction();

                $studentId = $this->studentRepository->create([
                    'company_id' => $companyId,
                    'full_name' => $row['full_name'],
                    'email' => $row['email'] ?? null,
                    'document_number' => $row['document_number'] ?? null,
                ]);

                $validationHash = hash(
                    'sha256',
                    implode('|', [$companyId, $studentId, $row['certificate_code'], $row['completion_date'], microtime(true)])
                );
                $validationUrl = url(trim(config('app.certificate_validation_route'), '/') . '/' . $validationHash);

                $certificateId = $this->certificateRepository->create([
                    'company_id' => $companyId,
                    'student_id' => $studentId,
                    'template_id' => (int) $template['id'],
                    'batch_id' => $batchId,
                    'course_name' => $row['course_name'],
                    'workload_hours' => $row['workload_hours'],
                    'completion_date' => $row['completion_date'],
                    'certificate_code' => $row['certificate_code'],
                    'instructor_name' => $row['instructor_name'],
                    'institution_name' => $row['institution_name'],
                    'program_content' => $programContent['content'],
                    'validation_hash' => $validationHash,
                    'validation_url' => $validationUrl,
                    'status' => 'pending',
                    'metadata_json' => json_encode([
                        'source_type' => $sourceType,
                        'program_sections' => $programContent['sections'],
                        'program_character_count' => $programContent['character_count'],
                        'program_estimated_lines' => $programContent['estimated_lines'],
                    ], JSON_UNESCAPED_UNICODE),
                ]);

                $db->commit();

                $qrCode = $this->qrCodeService->dataUri($validationUrl);
                $html = render('certificates/pdf', [
                    'template' => $template,
                    'certificate' => [
                        'student_name' => $row['full_name'],
                        'course_name' => $row['course_name'],
                        'workload_hours' => $row['workload_hours'],
                        'completion_date' => $row['completion_date'],
                        'certificate_code' => $row['certificate_code'],
                        'instructor_name' => $row['instructor_name'],
                        'institution_name' => $row['institution_name'],
                        'program_content' => $programContent['content'],
                        'program_sections' => $programContent['sections'],
                        'validation_url' => $validationUrl,
                    ],
                    'qrCode' => $qrCode,
                ], '');

                $filename = sprintf(
                    '%s-%s.pdf',
                    preg_replace('/[^a-z0-9-]+/i', '-', strtolower($row['certificate_code'])) ?: 'certificado',
                    preg_replace('/[^a-z0-9-]+/i', '-', strtolower($row['full_name'])) ?: 'aluno'
                );
                $absoluteFile = rtrim((string) config('app.public_storage_path'), '/\\') . DIRECTORY_SEPARATOR . $filename;
                $relativeFile = 'storage/certificados/' . $filename;

                $this->pdfGeneratorService->generate($html, $absoluteFile);
                $this->certificateRepository->markGenerated($certificateId, $relativeFile);

                $generated[] = [
                    'id' => $certificateId,
                    'file' => $relativeFile,
                    'name' => $row['full_name'],
                    'certificate_code' => $row['certificate_code'],
                ];

                $this->historyRepository->create([
                    'company_id' => $companyId,
                    'batch_id' => $batchId,
                    'certificate_id' => $certificateId,
                    'action' => 'certificate.generated',
                    'status' => 'success',
                    'message' => sprintf('Certificado gerado para %s.', $row['full_name']),
                ]);
            } catch (Throwable $throwable) {
                if (Database::connection()->inTransaction()) {
                    Database::connection()->rollBack();
                }

                $errorMessage = $this->normalizeErrorMessage($throwable, $row);

                if ($certificateId !== null) {
                    $this->certificateRepository->markFailed($certificateId);
                }

                $this->historyRepository->create([
                    'company_id' => $companyId,
                    'batch_id' => $batchId,
                    'certificate_id' => $certificateId,
                    'action' => 'certificate.failed',
                    'status' => 'error',
                    'message' => sprintf('Falha ao gerar certificado: %s', $errorMessage),
                    'payload_json' => json_encode(['row' => $row], JSON_UNESCAPED_UNICODE),
                ]);

                $failures[] = $errorMessage;
            }

            $status = $processed >= count($rows) ? 'completed' : 'processing';
            $this->batchRepository->updateProgress($batchId, $processed, $status);
        }

        $zipFile = (new ZipExportService($this->certificateRepository))->exportBatch($batchId);

        return [
            'batch_id' => $batchId,
            'generated_count' => count($generated),
            'total_count' => count($rows),
            'files' => $generated,
            'failed_count' => count($failures),
            'failed_messages' => $failures,
            'zip_file' => $zipFile,
        ];
    }

    private function normalizeErrorMessage(Throwable $throwable, array $row): string
    {
        if ($throwable instanceof PDOException && $throwable->getCode() === '23000') {
            return sprintf(
                'O codigo do certificado "%s" ja existe na base. Gere um novo codigo ou deixe o campo em branco para numeracao automatica.',
                (string) ($row['certificate_code'] ?? 'N/D')
            );
        }

        return $throwable->getMessage();
    }
}
