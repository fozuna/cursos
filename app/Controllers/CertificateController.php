<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CertificateRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Services\CertificateCodeService;
use App\Services\CertificateGenerationService;
use App\Services\CourseCatalogService;
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
        private readonly CourseCatalogService $courseCatalogService = new CourseCatalogService(),
        private readonly CertificateGenerationService $certificateGenerationService = new CertificateGenerationService(),
        private readonly CertificateRepository $certificateRepository = new CertificateRepository(),
        private readonly CompanyRepository $companyRepository = new CompanyRepository(),
        private readonly CourseRepository $courseRepository = new CourseRepository(),
        private readonly EnrollmentRepository $enrollmentRepository = new EnrollmentRepository()
    ) {
    }

    public function index(Request $request, array $params = []): never
    {
        unset($params);

        $company = $this->companyRepository->findDefault();

        if ($company === null) {
            throw new InvalidArgumentException('Empresa padrao nao encontrada.');
        }

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        $lastRequestedBy = $_SESSION['last_requested_by'] ?? 'Operador do painel';

        $section = (string) $request->input('secao', 'gerar');
        $activeSubNav = $section === 'lista' ? 'list' : 'generate';

        $this->view('certificates.index', [
            'company' => $company,
            'flash' => $flash,
            'stats' => $this->certificateRepository->statsByCompany((int) $company['id']),
            'activeCourses' => $this->courseRepository->allActiveByCompany((int) $company['id']),
            'latestCertificates' => $this->certificateRepository->latestByCompany((int) $company['id'], 24),
            'nextCertificateCode' => $this->certificateCodeService->nextAvailableCode(),
            'activeSubNav' => $activeSubNav,
            'lastRequestedBy' => (string) $lastRequestedBy,
        ]);
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
            $requestedBy = trim((string) $request->input('requested_by', ''));
            $requestedBy = $requestedBy !== '' ? $requestedBy : 'Operador do painel';
            $_SESSION['last_requested_by'] = $requestedBy;

            Logger::info('certificates.generate.request', [
                'mode' => $mode,
                'template_id' => $templateId,
                'request_method' => $request->method(),
                'request_path' => $request->path(),
                'requested_by' => $requestedBy,
                'requested_ip' => method_exists($request, 'ip') ? $request->ip() : null,
            ]);

            if ($mode === 'course') {
                [$imported, $programContent] = $this->buildFromCourseSelection($request, (int) $company['id']);
            } else {
                $imported = $mode === 'upload'
                    ? $this->studentImportService->fromUpload($request->file('students_file') ?? [])
                    : $this->studentImportService->fromManual($request->all());
                $programContent = $this->programContentService->buildFromPayload($request->all());
            }

            $preparedRows = $this->certificateCodeService->prepareRows($imported['rows']);

            $result = $this->certificateGenerationService->generate(
                $preparedRows,
                $imported['source_type'],
                $imported['source_file'],
                (int) $company['id'],
                $programContent,
                $templateId,
                $requestedBy,
                method_exists($request, 'ip') ? $request->ip() : null
            );

            $_SESSION['flash'] = [
                'type' => ($result['generated_count'] > 0 || ($result['blocked_count'] ?? 0) > 0) ? 'success' : 'error',
                'message' => $result['generated_count'] > 0
                    ? sprintf(
                        '%d de %d certificados gerados com sucesso.',
                        $result['generated_count'],
                        $result['total_count']
                    )
                    : (($result['blocked_count'] ?? 0) > 0
                        ? sprintf(
                            'Nenhum certificado novo foi gerado. %d item(ns) do lote foram bloqueados por ja terem sido emitidos.',
                            (int) ($result['blocked_count'] ?? 0)
                        )
                        : ($result['failed_messages'][0] ?? 'Nenhum certificado foi gerado.')),
                'details' => sprintf(
                    'Entrada: %s%s. Participantes processados: %d. Gerados: %d. Bloqueados: %d. Falhas: %d.',
                    strtoupper($imported['source_type']),
                    !empty($imported['original_name']) ? ' - ' . $imported['original_name'] : '',
                    (int) ($imported['row_count'] ?? count($preparedRows)),
                    (int) ($result['generated_count'] ?? 0),
                    (int) ($result['blocked_count'] ?? 0),
                    (int) ($result['failed_count'] ?? 0)
                ),
                'input_mode' => $mode,
                'program_fields' => $request->all(),
                'zip_file' => $result['zip_file'],
                'report' => [
                    'generated_items' => $result['generated_items'] ?? [],
                    'blocked_items' => $result['blocked_items'] ?? [],
                    'failed_messages' => $result['failed_messages'] ?? [],
                    'requested_by' => $requestedBy,
                ],
            ];

            Logger::info('certificates.generate.success', [
                'mode' => $mode,
                'source_type' => $imported['source_type'],
                'source_file' => $imported['source_file'],
                'row_count' => $imported['row_count'] ?? count($preparedRows),
                'program_character_count' => $programContent['character_count'],
                'program_estimated_lines' => $programContent['estimated_lines'],
                'generated_count' => $result['generated_count'],
                'blocked_count' => $result['blocked_count'] ?? 0,
                'failed_count' => $result['failed_count'] ?? 0,
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

        redirect((string) $request->input('redirect_to', '/'));
    }

    public function download(Request $request, array $params = []): never
    {
        unset($request);

        $file = basename((string) ($params['file'] ?? ''));
        $path = public_path('storage/certificados/' . $file);

        Response::download($path, $file);
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private function buildFromCourseSelection(Request $request, int $companyId): array
    {
        $courseId = (int) $request->input('course_id', 0);
        $course = $this->courseRepository->findActive($courseId);

        if ($course === null || (int) $course['company_id'] !== $companyId) {
            throw new InvalidArgumentException('Selecione um curso valido para gerar certificados.');
        }

        $enrollments = $this->enrollmentRepository->completedByCourse($courseId);

        if ($enrollments === []) {
            throw new InvalidArgumentException('Nao existem matriculas concluidas para o curso selecionado.');
        }

        $rows = array_map(static function (array $enrollment) use ($course): array {
            return [
                'student_id' => (int) $enrollment['student_id'],
                'full_name' => (string) $enrollment['full_name'],
                'email' => $enrollment['email'] ?? null,
                'phone' => $enrollment['phone'] ?? null,
                'cpf' => $enrollment['cpf'] ?? null,
                'document_number' => $enrollment['cpf'] ?? null,
                'course_name' => (string) $course['name'],
                'workload_hours' => (int) $course['workload_hours'],
                'completion_date' => substr((string) ($enrollment['completed_at'] ?: $course['end_date']), 0, 10),
                'certificate_code' => '',
                'certificate_prefix' => (string) ($course['certificate_prefix'] ?? 'CERT'),
                'instructor_name' => (string) $course['instructor_name'],
                'institution_name' => (string) $course['institution_name'],
            ];
        }, $enrollments);

        return [[
            'source_file' => null,
            'original_name' => null,
            'source_type' => 'course',
            'row_count' => count($rows),
            'rows' => $rows,
        ], $this->courseCatalogService->buildProgramContentFromCourse($course)];
    }
}
