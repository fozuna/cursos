<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CompanyRepository;
use App\Services\CertificateCodeService;
use App\Services\CertificateGenerationService;
use App\Services\ProgramContentService;
use App\Services\StudentImportService;
use App\Support\Logger;
use InvalidArgumentException;
use Throwable;

final class CertificateController extends Controller
{
    public function __construct(
        private readonly StudentImportService $studentImportService = new StudentImportService(),
        private readonly CertificateCodeService $certificateCodeService = new CertificateCodeService(),
        private readonly ProgramContentService $programContentService = new ProgramContentService(),
        private readonly CertificateGenerationService $certificateGenerationService = new CertificateGenerationService(),
        private readonly CompanyRepository $companyRepository = new CompanyRepository()
    ) {
    }

    public function generate(Request $request, array $params = []): never
    {
        unset($params);

        try {
            $company = $this->companyRepository->findDefault();

            if ($company === null) {
                throw new InvalidArgumentException('Empresa padrao nao encontrada.');
            }

            $templateId = (int) $request->input('template_id', 0) ?: null;
            $mode = (string) $request->input('input_mode', 'manual');

            Logger::info('certificates.generate.request', [
                'mode' => $mode,
                'template_id' => $templateId,
                'request_method' => $request->method(),
                'request_path' => $request->path(),
            ]);

            $imported = $mode === 'upload'
                ? $this->studentImportService->fromUpload($request->file('students_file') ?? [])
                : $this->studentImportService->fromManual($request->all());
            $preparedRows = $this->certificateCodeService->prepareRows($imported['rows']);

            $programContent = $this->programContentService->buildFromPayload($request->all());

            $result = $this->certificateGenerationService->generate(
                $preparedRows,
                $imported['source_type'],
                $imported['source_file'],
                (int) $company['id'],
                $programContent,
                $templateId
            );

            $_SESSION['flash'] = [
                'type' => $result['generated_count'] > 0 ? 'success' : 'error',
                'message' => $result['generated_count'] > 0
                    ? sprintf(
                        '%d de %d certificados gerados com sucesso.',
                        $result['generated_count'],
                        $result['total_count']
                    )
                    : ($result['failed_messages'][0] ?? 'Nenhum certificado foi gerado.'),
                'details' => sprintf(
                    'Entrada: %s%s. Participantes processados: %d. Falhas: %d.',
                    strtoupper($imported['source_type']),
                    !empty($imported['original_name']) ? ' - ' . $imported['original_name'] : '',
                    (int) ($imported['row_count'] ?? count($preparedRows)),
                    (int) ($result['failed_count'] ?? 0)
                ),
                'input_mode' => $mode,
                'program_fields' => $request->all(),
                'zip_file' => $result['zip_file'],
            ];

            Logger::info('certificates.generate.success', [
                'mode' => $mode,
                'source_type' => $imported['source_type'],
                'source_file' => $imported['source_file'],
                'row_count' => $imported['row_count'] ?? count($preparedRows),
                'program_character_count' => $programContent['character_count'],
                'program_estimated_lines' => $programContent['estimated_lines'],
                'generated_count' => $result['generated_count'],
                'batch_id' => $result['batch_id'],
                'zip_file' => $result['zip_file'],
            ]);
        } catch (Throwable $throwable) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => $throwable->getMessage(),
                'details' => 'Consulte o log da aplicacao em storage/logs/app.log para diagnostico detalhado.',
                'input_mode' => $request->input('input_mode', 'manual'),
                'program_fields' => $request->all(),
            ];

            Logger::error('certificates.generate.failed', [
                'error' => $throwable->getMessage(),
                'trace' => $throwable->getTraceAsString(),
            ]);
        }

        redirect('/');
    }

    public function download(Request $request, array $params = []): never
    {
        unset($request);

        $file = basename((string) ($params['file'] ?? ''));
        $path = public_path('storage/certificados/' . $file);

        Response::download($path, $file);
    }
}
