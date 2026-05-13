<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CompanyRepository;
use App\Repositories\CourseRepository;
use App\Services\CourseCatalogService;
use App\Services\ProgramContentService;
use App\Services\ReportExportService;
use InvalidArgumentException;
use Throwable;

final class CourseController extends Controller
{
    public function __construct(
        private readonly CompanyRepository $companyRepository = new CompanyRepository(),
        private readonly CourseRepository $courseRepository = new CourseRepository(),
        private readonly CourseCatalogService $courseCatalogService = new CourseCatalogService(),
        private readonly ProgramContentService $programContentService = new ProgramContentService(),
        private readonly ReportExportService $reportExportService = new ReportExportService()
    ) {
    }

    public function index(Request $request, array $params = []): never
    {
        unset($params);

        $company = $this->requireCompany();
        $search = trim((string) $request->input('search', ''));
        $page = max(1, (int) $request->input('page', 1));
        $editId = (int) $request->input('edit', 0);
        $editingCourse = $editId > 0 ? $this->courseRepository->findActive($editId) : null;
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $this->view('courses.index', [
            'company' => $company,
            'flash' => $flash,
            'courses' => $this->courseRepository->paginateByCompany((int) $company['id'], $search, $page),
            'editingCourse' => $editingCourse,
            'search' => $search,
            'programSectionLabels' => $this->programContentService->labels(),
            'programSectionDefaults' => $this->programContentService->defaultSections(),
            'activeNav' => 'courses',
        ]);
    }

    public function save(Request $request, array $params = []): never
    {
        unset($params);

        try {
            $company = $this->requireCompany();
            $courseId = (int) $request->input('course_id', 0);
            $payload = $this->courseCatalogService->validateAndNormalize($request->all(), (string) $company['name']);

            if ($courseId > 0) {
                $this->courseRepository->update($courseId, $payload);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Curso atualizado com sucesso.'];
            } else {
                $payload['company_id'] = (int) $company['id'];
                $this->courseRepository->create($payload);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Curso cadastrado com sucesso.'];
            }
        } catch (Throwable $throwable) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $throwable->getMessage()];
        }

        redirect('/cursos');
    }

    public function delete(Request $request, array $params = []): never
    {
        unset($request);

        try {
            $courseId = (int) ($params['id'] ?? 0);

            if ($courseId <= 0) {
                throw new InvalidArgumentException('Curso invalido para exclusao.');
            }

            if ($this->courseRepository->countActiveEnrollments($courseId) > 0) {
                throw new InvalidArgumentException('Nao e possivel excluir cursos com matriculas ativas.');
            }

            $this->courseRepository->softDelete($courseId);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Curso removido com sucesso.'];
        } catch (Throwable $throwable) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $throwable->getMessage()];
        }

        redirect('/cursos');
    }

    public function exportCsv(Request $request, array $params = []): never
    {
        unset($params);
        $company = $this->requireCompany();
        $search = trim((string) $request->input('search', ''));
        $result = $this->courseRepository->paginateByCompany((int) $company['id'], $search, 1, 500);
        $export = $this->reportExportService->exportCsv('cursos', [
            'Curso', 'Carga horaria', 'Inicio', 'Termino', 'Instrutor', 'Instituicao', 'Ativo'
        ], array_map(
            static fn (array $course): array => [
                $course['name'],
                $course['workload_hours'],
                format_date_br((string) $course['start_date']),
                format_date_br((string) $course['end_date']),
                $course['instructor_name'],
                $course['institution_name'],
                (int) $course['is_active'] === 1 ? 'Sim' : 'Nao',
            ],
            $result['items']
        ));

        Response::download($export['path'], $export['name']);
    }

    public function exportPdf(Request $request, array $params = []): never
    {
        unset($params);
        $company = $this->requireCompany();
        $search = trim((string) $request->input('search', ''));
        $result = $this->courseRepository->paginateByCompany((int) $company['id'], $search, 1, 500);
        $export = $this->reportExportService->exportPdf('Relatorio de Cursos', [
            'Curso', 'Carga horaria', 'Periodo', 'Instrutor', 'Ativo'
        ], array_map(
            static fn (array $course): array => [
                $course['name'],
                $course['workload_hours'] . 'h',
                format_date_br((string) $course['start_date']) . ' a ' . format_date_br((string) $course['end_date']),
                $course['instructor_name'],
                (int) $course['is_active'] === 1 ? 'Sim' : 'Nao',
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
