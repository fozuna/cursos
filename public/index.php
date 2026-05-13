<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Environment;
use App\Support\Logger;

session_start();

require dirname(__DIR__) . '/vendor/autoload.php';

Environment::load(dirname(__DIR__) . '/.env');
$appConfig = require dirname(__DIR__) . '/config/app.php';
date_default_timezone_set((string) $appConfig['timezone']);

$router = new Router();
require dirname(__DIR__) . '/routes/web.php';

try {
    $router->dispatch(Request::capture());
} catch (Throwable $throwable) {
    Logger::error('application.unhandled_exception', [
        'message' => $throwable->getMessage(),
        'trace' => $throwable->getTraceAsString(),
    ]);

    $message = config('app.debug') ? $throwable->getMessage() : 'Erro interno no sistema.';
    Response::html(render('partials.500', ['message' => $message]), 500);
}
