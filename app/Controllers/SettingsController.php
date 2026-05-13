<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Repositories\CompanyRepository;

final class SettingsController extends Controller
{
    public function __construct(
        private readonly CompanyRepository $companyRepository = new CompanyRepository()
    ) {
    }

    public function index(Request $request, array $params = []): never
    {
        unset($request, $params);

        $this->view('settings.index', [
            'company' => $this->companyRepository->findDefault(),
            'appConfig' => config('app'),
        ]);
    }
}
