<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Services\PdfGeneratorService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PdfGenerationServiceTest extends TestCase
{
    #[DataProvider('frontLayoutProvider')]
    public function testPdfGeneratorCreatesTwoPageCertificate(array $certificate): void
    {
        $service = new PdfGeneratorService();
        $targetFile = tempnam(sys_get_temp_dir(), 'certificado_');

        self::assertNotFalse($targetFile);

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

        $contents = file_get_contents($targetFile);

        self::assertNotFalse($contents);
        self::assertFileExists($targetFile);
        self::assertMatchesRegularExpression('/\/Count\s+2\b/', $contents);

        @unlink($targetFile);
    }

    /**
     * @return array<string, array{0: array<string, mixed>}>
     */
    public static function frontLayoutProvider(): array
    {
        return [
            'conteudo padrao' => [[
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
            ]],
            'nomes extensos' => [[
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
            ]],
        ];
    }
}
