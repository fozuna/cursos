<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
App\Core\Environment::load(dirname(__DIR__) . '/.env');

$service = new App\Services\PdfGeneratorService();

$cases = [
    'padrao' => [
        'student_name' => 'Teste Integrado PDF',
        'course_name' => 'Curso de Auditoria',
        'workload_hours' => 20,
        'completion_date' => '2026-05-13',
        'certificate_code' => 'CERT-PDF-0001',
        'instructor_name' => 'Instrutor Integrado',
        'institution_name' => 'Academia Corporativa Premium',
        'program_content' => 'Conteudo programatico completo',
        'program_sections' => [
            'syllabus' => 'Conceitos centrais e aplicacao pratica.',
            'objectives' => 'Aplicar tecnicas com seguranca e criterio.',
            'modules' => "Modulo 1 - Base\nModulo 2 - Oficina",
            'methodology' => 'Aulas, estudos de caso e exercicios.',
            'evaluation' => 'Participacao e atividade final.',
        ],
        'validation_url' => 'http://localhost/cursos/public/validar/hash-pdf',
    ],
    'extenso' => [
        'student_name' => 'Fernanda Cristina Albuquerque de Souza e Lima',
        'course_name' => 'Programa Avancado de Governanca, Compliance e Excelencia Operacional em Ambientes Corporativos de Alta Complexidade',
        'workload_hours' => 120,
        'completion_date' => '2026-05-13',
        'certificate_code' => 'CERT-PDF-0002',
        'instructor_name' => 'Prof. Dr. Alexandre Fernando Monteiro dos Santos',
        'institution_name' => 'Academia Corporativa Premium e Instituto de Desenvolvimento Executivo',
        'program_content' => 'Conteudo programatico completo',
        'program_sections' => [
            'syllabus' => 'Conceitos centrais, normas aplicaveis e aplicacao pratica em cenarios corporativos.',
            'objectives' => 'Desenvolver capacidade analitica, controle e execucao de rotinas de governanca.',
            'modules' => "Modulo 1 - Fundamentos\nModulo 2 - Compliance\nModulo 3 - Indicadores\nModulo 4 - Casos praticos",
            'methodology' => 'Aulas expositivas, oficinas, simulacoes e estudos de caso.',
            'evaluation' => 'Participacao, atividade aplicada e analise final de caso.',
        ],
        'validation_url' => 'http://localhost/cursos/public/validar/hash-pdf-extenso',
    ],
];

$results = [];

foreach ($cases as $name => $certificate) {
    $targetFile = dirname(__DIR__) . '/storage/' . $name . '.pdf';

    $html = render('certificates/pdf', [
        'template' => [
            'settings_json' => json_encode([
                'accentColor' => '#1d4ed8',
                'title' => 'Certificado de Conclusao',
                'subtitle' => 'Reconhecimento oficial de participacao e aproveitamento',
                'footer' => 'Documento validavel online.',
            ], JSON_UNESCAPED_UNICODE),
        ],
        'certificate' => $certificate,
        'qrCode' => 'data:image/svg+xml;base64,PHN2Zy8+',
    ], '');

    $service->generate($html, $targetFile);
    $contents = file_get_contents($targetFile) ?: '';
    preg_match_all('/\/Count\s+(\d+)/', $contents, $matches);

    $results[$name] = [
        'count_values' => $matches[1] ?? [],
        'page_markers' => substr_count($contents, '/Type /Page'),
    ];
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
