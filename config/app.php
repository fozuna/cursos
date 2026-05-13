<?php

declare(strict_types=1);

$defaultPublicPath = is_dir(dirname(__DIR__) . '/public')
    ? dirname(__DIR__) . '/public'
    : dirname(__DIR__);

return [
    'name' => 'CertificaPro SaaS',
    'base_url' => $_ENV['APP_URL'] ?? 'http://localhost/cursos/public',
    'use_index' => filter_var($_ENV['APP_USE_INDEX'] ?? false, FILTER_VALIDATE_BOOL),
    'env' => $_ENV['APP_ENV'] ?? 'local',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOL),
    'timezone' => 'America/Sao_Paulo',
    'default_company_id' => 1,
    'storage_path' => dirname(__DIR__) . '/storage',
    'public_path' => $_ENV['APP_PUBLIC_PATH'] ?? $defaultPublicPath,
    'public_storage_path' => $_ENV['APP_PUBLIC_STORAGE_PATH'] ?? ($defaultPublicPath . '/storage/certificados'),
    'upload_path' => $_ENV['APP_UPLOAD_PATH'] ?? ($defaultPublicPath . '/uploads'),
    'institution' => [
        'cnpj' => $_ENV['INSTITUTION_CNPJ'] ?? '30.358.115/0001-13',
        'logo_path' => $_ENV['INSTITUTION_LOGO_PATH'] ?? 'assets/images/logo-escura.png',
    ],
    'certificate_validation_route' => '/validar',
    'pdf' => [
        'paper' => 'a4',
        'orientation' => 'landscape',
        'dpi' => 150,
        'default_font' => 'DejaVu Sans',
    ],
];
