<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;

abstract class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'layouts/app'): never
    {
        Response::html(render($view, $data, $layout));
    }

    protected function json(array $payload, int $status = 200): never
    {
        Response::json($payload, $status);
    }
}
