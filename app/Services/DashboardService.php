<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\BatchRepository;
use App\Repositories\CertificateRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\HistoryRepository;
use App\Repositories\StudentRepository;
use App\Repositories\TemplateRepository;

final class DashboardService
{
    public function __construct(
        private readonly CompanyRepository $companyRepository = new CompanyRepository(),
        private readonly TemplateRepository $templateRepository = new TemplateRepository(),
        private readonly CertificateRepository $certificateRepository = new CertificateRepository(),
        private readonly CertificateCodeService $certificateCodeService = new CertificateCodeService(),
        private readonly BatchRepository $batchRepository = new BatchRepository(),
        private readonly HistoryRepository $historyRepository = new HistoryRepository(),
        private readonly ProgramContentService $programContentService = new ProgramContentService(),
        private readonly CourseRepository $courseRepository = new CourseRepository(),
        private readonly StudentRepository $studentRepository = new StudentRepository(),
        private readonly EnrollmentRepository $enrollmentRepository = new EnrollmentRepository()
    ) {
    }

    public function build(): array
    {
        $company = $this->companyRepository->findDefault();

        if ($company === null) {
            throw new \RuntimeException('Empresa padrao nao configurada.');
        }

        return [
            'company' => $company,
            'templates' => $this->templateRepository->allByCompany((int) $company['id']),
            'stats' => $this->certificateRepository->statsByCompany((int) $company['id']),
            'nextCertificateCode' => $this->certificateCodeService->nextAvailableCode(),
            'latestCertificates' => $this->certificateRepository->latestByCompany((int) $company['id']),
            'latestBatches' => $this->batchRepository->latestByCompany((int) $company['id']),
            'history' => $this->historyRepository->latestByCompany((int) $company['id']),
            'programSectionLabels' => $this->programContentService->labels(),
            'programSectionDefaults' => $this->programContentService->defaultSections(),
            'courseCount' => $this->courseRepository->countByCompany((int) $company['id']),
            'studentCount' => $this->studentRepository->countByCompany((int) $company['id']),
            'enrollmentCount' => $this->enrollmentRepository->countByCompany((int) $company['id']),
            'activeCourses' => $this->courseRepository->allActiveByCompany((int) $company['id']),
            'recentEnrollments' => $this->enrollmentRepository->paginateByCompany((int) $company['id'], [], 1, 6)['items'],
        ];
    }
}
