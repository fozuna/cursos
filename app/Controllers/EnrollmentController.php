<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CompanyRepository;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\StudentRepository;
use App\Services\EnrollmentManagementService;
use App\Services\ReportExportService;
use InvalidArgumentException;
use Throwable;

final class EnrollmentController extends Controller
{
    public function __construct(
        private readonly CompanyRepository $companyRepository = new CompanyRepository(),
        private readonly EnrollmentRepository $enrollmentRepository = new EnrollmentRepository(),
        private readonly CourseRepository $courseRepository = new CourseRepository(),
        private readonly StudentRepository $studentRepository = new StudentRepository(),
        private readonly EnrollmentManagementService $enrollmentManagementService = new EnrollmentManagementService(),
        private readonly ReportExportService $reportExportService = new ReportExportService()
    ) {
    }

    public function index(Request $request, array $params = []): never
    {
        unset($params);

        $company = $this->requireCompany();
        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'status' => trim((string) $request->input('status', '')),
            'course_id' => (int) $request->input('course_id', 0),
            'student_id' => (int) $request->input('student_id', 0),
        ];
        $page = max(1, (int) $request->input('page', 1));
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $this->view('enrollments.index', [
            'company' => $company,
            'flash' => $flash,
            'filters' => $filters,
            'enrollments' => $this->enrollmentRepository->paginateByCompany((int) $company['id'], $filters, $page),
            'courses' => $this->courseRepository->allActiveByCompany((int) $company['id']),
            'students' => $this->studentRepository->allActiveByCompany((int) $company['id']),
            'courseReport' => $filters['course_id'] > 0 ? $this->enrollmentRepository->byCourse($filters['course_id']) : [],
            'studentReport' => $filters['student_id'] > 0 ? $this->enrollmentRepository->byStudent($filters['student_id']) : [],
            'activeNav' => 'enrollments',
        ]);
    }

    public function save(Request $request, array $params = []): never
    {
        unset($params);

        try {
            $company = $this->requireCompany();
            $courseId = (int) $request->input('course_id', 0);
            $studentIds = $request->input('student_ids', []);
            $status = (string) $request->input('status', 'active');
            $enrolledAt = (string) $request->input('enrolled_at', date('Y-m-d H:i:s'));
            $completedAt = $request->input('completed_at') ? (string) $request->input('completed_at') : null;

            if (!is_array($studentIds) || $studentIds === []) {
                throw new InvalidArgumentException('Selecione pelo menos um aluno para matricular.');
            }

            $result = $this->enrollmentManagementService->enrollMany(
                (int) $company['id'],
                $courseId,
                $studentIds,
                $status,
                $enrolledAt,
                $completedAt
            );

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => sprintf('Matriculas criadas: %d. Ignoradas: %d.', $result['created'], $result['skipped']),
            ];
        } catch (Throwable $throwable) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $throwable->getMessage()];
        }

        redirect('/matriculas');
    }

    public function updateStatus(Request $request, array $params = []): never
    {
        try {
            $enrollmentId = (int) ($params['id'] ?? 0);
            $validated = $this->enrollmentManagementService->validateStatusUpdate(
                (string) $request->input('status', ''),
                $request->input('completed_at') ? (string) $request->input('completed_at') : null
            );

            $this->enrollmentRepository->updateStatus($enrollmentId, $validated['status'], $validated['completed_at']);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Status da matricula atualizado com sucesso.'];
        } catch (Throwable $throwable) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $throwable->getMessage()];
        }

        redirect('/matriculas');
    }

    public function exportCsv(Request $request, array $params = []): never
    {
        unset($params);
        $company = $this->requireCompany();
        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'status' => trim((string) $request->input('status', '')),
            'course_id' => (int) $request->input('course_id', 0),
            'student_id' => (int) $request->input('student_id', 0),
        ];
        $result = $this->enrollmentRepository->paginateByCompany((int) $company['id'], $filters, 1, 1000);
        $export = $this->reportExportService->exportCsv('matriculas', [
            'Aluno', 'Curso', 'Status', 'Matricula', 'Conclusao'
        ], array_map(
            static fn (array $item): array => [
                $item['full_name'],
                $item['course_name'],
                $item['status'],
                format_date_br(substr((string) $item['enrolled_at'], 0, 10)),
                $item['completed_at'] ? format_date_br(substr((string) $item['completed_at'], 0, 10)) : '',
            ],
            $result['items']
        ));

        Response::download($export['path'], $export['name']);
    }

    public function exportPdf(Request $request, array $params = []): never
    {
        unset($params);
        $company = $this->requireCompany();
        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'status' => trim((string) $request->input('status', '')),
            'course_id' => (int) $request->input('course_id', 0),
            'student_id' => (int) $request->input('student_id', 0),
        ];
        $result = $this->enrollmentRepository->paginateByCompany((int) $company['id'], $filters, 1, 1000);
        $export = $this->reportExportService->exportPdf('Relatorio de Matriculas', [
            'Aluno', 'Curso', 'Status', 'Matricula', 'Conclusao'
        ], array_map(
            static fn (array $item): array => [
                $item['full_name'],
                $item['course_name'],
                $item['status'],
                format_date_br(substr((string) $item['enrolled_at'], 0, 10)),
                $item['completed_at'] ? format_date_br(substr((string) $item['completed_at'], 0, 10)) : '',
            ],
            $result['items']
        ));

        Response::download($export['path'], $export['name']);
    }

    private function requireCompany(): array
    {
        $company = $this->companyRepository->findDefault();

        if ($company === null) {
            throw new InvalidArgumentException('Empresa padrao nao configurada.');
        }

        return $company;
    }
}
