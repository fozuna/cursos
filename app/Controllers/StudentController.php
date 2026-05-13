<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CompanyRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\StudentRepository;
use App\Services\ReportExportService;
use App\Services\StudentProfileService;
use InvalidArgumentException;
use Throwable;

final class StudentController extends Controller
{
    public function __construct(
        private readonly CompanyRepository $companyRepository = new CompanyRepository(),
        private readonly StudentRepository $studentRepository = new StudentRepository(),
        private readonly EnrollmentRepository $enrollmentRepository = new EnrollmentRepository(),
        private readonly StudentProfileService $studentProfileService = new StudentProfileService(),
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
        $editingStudent = $editId > 0 ? $this->studentRepository->findActive($editId) : null;
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $this->view('students.index', [
            'company' => $company,
            'flash' => $flash,
            'students' => $this->studentRepository->paginateByCompany((int) $company['id'], $search, $page),
            'editingStudent' => $editingStudent,
            'search' => $search,
            'activeNav' => 'students',
        ]);
    }

    public function save(Request $request, array $params = []): never
    {
        unset($params);

        try {
            $company = $this->requireCompany();
            $studentId = (int) $request->input('student_id', 0);
            $payload = $this->studentProfileService->validateAndNormalize($request->all(), (int) $company['id'], $studentId ?: null);

            if ($studentId > 0) {
                $this->studentRepository->update($studentId, $payload);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Aluno atualizado com sucesso.'];
            } else {
                $payload['company_id'] = (int) $company['id'];
                $this->studentRepository->create($payload);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Aluno cadastrado com sucesso.'];
            }
        } catch (Throwable $throwable) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $throwable->getMessage()];
        }

        redirect('/alunos');
    }

    public function delete(Request $request, array $params = []): never
    {
        unset($request);

        try {
            $studentId = (int) ($params['id'] ?? 0);

            if ($studentId <= 0) {
                throw new InvalidArgumentException('Aluno invalido para exclusao.');
            }

            if ($this->enrollmentRepository->countByStudent($studentId) > 0) {
                throw new InvalidArgumentException('Nao e possivel excluir alunos com matriculas vinculadas.');
            }

            $this->studentRepository->softDelete($studentId);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Aluno removido com sucesso.'];
        } catch (Throwable $throwable) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $throwable->getMessage()];
        }

        redirect('/alunos');
    }

    public function exportCsv(Request $request, array $params = []): never
    {
        unset($params);
        $company = $this->requireCompany();
        $search = trim((string) $request->input('search', ''));
        $result = $this->studentRepository->paginateByCompany((int) $company['id'], $search, 1, 1000);
        $export = $this->reportExportService->exportCsv('alunos', [
            'Nome', 'E-mail', 'Telefone', 'CPF'
        ], array_map(
            static fn (array $student): array => [
                $student['full_name'],
                $student['email'],
                $student['phone'],
                $student['cpf'],
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
        $result = $this->studentRepository->paginateByCompany((int) $company['id'], $search, 1, 1000);
        $export = $this->reportExportService->exportPdf('Relatorio de Alunos', [
            'Nome', 'E-mail', 'Telefone', 'CPF'
        ], array_map(
            static fn (array $student): array => [
                $student['full_name'],
                $student['email'],
                $student['phone'],
                $student['cpf'],
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
