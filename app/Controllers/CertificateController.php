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

            Logger::info('certificates.generate.request', [
                'mode' => $mode,
                'template_id' => $templateId,
                'request_method' => $request->method(),
                'request_path' => $request->path(),
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
