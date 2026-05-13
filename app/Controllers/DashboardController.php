<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Services\DashboardService;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService = new DashboardService()
    ) {
    }

    public function index(Request $request, array $params = []): never
    {
        unset($request, $params);

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $this->view('dashboard.index', array_merge($this->dashboardService->build(), ['flash' => $flash]));
    }
}
