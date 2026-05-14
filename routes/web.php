<?php

declare(strict_types=1);

use App\Controllers\CertificateController;
use App\Controllers\CourseController;
use App\Controllers\DashboardController;
use App\Controllers\EnrollmentController;
use App\Controllers\PublicCertificateAccessController;
use App\Controllers\SettingsController;
use App\Controllers\StudentController;
use App\Controllers\ValidationController;
use App\Core\Router;

/** @var Router $router */

$router->get('/', [DashboardController::class, 'index']);
$router->get('/cursos', [CourseController::class, 'index']);
$router->post('/cursos/salvar', [CourseController::class, 'save']);
$router->post('/cursos/excluir/{id}', [CourseController::class, 'delete']);
$router->get('/cursos/exportar/csv', [CourseController::class, 'exportCsv']);
$router->get('/cursos/exportar/pdf', [CourseController::class, 'exportPdf']);

$router->get('/alunos', [StudentController::class, 'index']);
$router->post('/alunos/salvar', [StudentController::class, 'save']);
$router->post('/alunos/excluir/{id}', [StudentController::class, 'delete']);
$router->get('/alunos/exportar/csv', [StudentController::class, 'exportCsv']);
$router->get('/alunos/exportar/pdf', [StudentController::class, 'exportPdf']);

$router->get('/matriculas', [EnrollmentController::class, 'index']);
$router->post('/matriculas/salvar', [EnrollmentController::class, 'save']);
$router->post('/matriculas/status/{id}', [EnrollmentController::class, 'updateStatus']);
$router->get('/matriculas/exportar/csv', [EnrollmentController::class, 'exportCsv']);
$router->get('/matriculas/exportar/pdf', [EnrollmentController::class, 'exportPdf']);

$router->get('/certificados', [CertificateController::class, 'index']);
$router->post('/certificados/gerar', [CertificateController::class, 'generate']);
$router->get('/download/{file}', [CertificateController::class, 'download']);
$router->get('/certificados-publicos/compartilhar/{id}', [PublicCertificateAccessController::class, 'share']);
$router->get('/certificados-publicos/acesso/{token}', [PublicCertificateAccessController::class, 'show']);
$router->post('/certificados-publicos/acesso/{token}', [PublicCertificateAccessController::class, 'authenticate']);
$router->get('/certificados-publicos/acesso/{token}/preview', [PublicCertificateAccessController::class, 'preview']);
$router->get('/certificados-publicos/acesso/{token}/download', [PublicCertificateAccessController::class, 'download']);
$router->get('/validar', [ValidationController::class, 'index']);
$router->get('/validar/{hash}', [ValidationController::class, 'show']);
$router->get('/configuracoes', [SettingsController::class, 'index']);
