<?php

declare(strict_types=1);

use App\Controllers\CertificateController;
use App\Controllers\DashboardController;
use App\Controllers\ValidationController;
use App\Core\Router;

/** @var Router $router */

$router->get('/', [DashboardController::class, 'index']);
$router->post('/certificados/gerar', [CertificateController::class, 'generate']);
$router->get('/download/{file}', [CertificateController::class, 'download']);
$router->get('/validar/{hash}', [ValidationController::class, 'show']);
