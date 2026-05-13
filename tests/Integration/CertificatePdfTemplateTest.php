<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CertificatePdfTemplateTest extends TestCase
{
    #[DataProvider('programContentProvider')]
    public function testTemplateRendersFrontAndBackPages(array $programSections): void
    {
        $html = render('certificates/pdf', [
            'template' => [
                'settings_json' => json_encode([
                    'accentColor' => '#1d4ed8',
                    'title' => 'Certificado de Conclusao',
                    'subtitle' => 'Reconhecimento oficial de participacao e aproveitamento',
                    'footer' => 'Documento validavel online.',
                ], JSON_UNESCAPED_UNICODE),
            ],
            'certificate' => [
                'student_name' => 'Bruno Henrique Alves',
                'course_name' => 'Gestao de Processos Corporativos',
                'workload_hours' => 24,
                'completion_date' => '2026-05-13',
                'certificate_code' => 'CERT-INTEGRACAO-001',
                'instructor_name' => 'Dra. Marina Campos',
                'institution_name' => 'Academia Corporativa Premium',
                'program_content' => implode("\n\n", $programSections),
                'program_sections' => $programSections,
                'validation_url' => 'http://localhost/cursos/public/validar/hash-teste',
            ],
            'qrCode' => 'data:image/svg+xml;base64,PHN2Zy8+',
        ], '');

        self::assertSame(2, substr_count($html, 'class="sheet"'));
        self::assertStringContainsString('Conteudo Programático', $html);
        self::assertStringContainsString('13/05/2026', $html);
        self::assertStringContainsString('CNPJ: 30.358.115/0001-13', $html);
        self::assertStringContainsString('page-break-after: always', $html);
    }

    /**
     * @return array<string, array{0: array<string, string>}>
     */
    public static function programContentProvider(): array
    {
        return [
            'conteudo enxuto' => [[
                'syllabus' => 'Conceitos essenciais e contextualizacao do tema.',
                'objectives' => 'Aplicar boas praticas no ambiente de trabalho.',
                'modules' => "Modulo 1 - Base conceitual\nModulo 2 - Aplicacao pratica",
                'methodology' => 'Aulas expositivas, exemplos e exercicios.',
                'evaluation' => 'Participacao e atividade final.',
            ]],
            'conteudo detalhado' => [[
                'syllabus' => 'Aborda conceitos, terminologia, fundamentos operacionais, riscos, controles e indicadores relacionados ao curso.',
                'objectives' => 'Desenvolver capacidade analitica, aplicacao pratica de tecnicas e tomada de decisao com foco em resultado.',
                'modules' => "Modulo 1 - Fundamentos\nModulo 2 - Ferramentas\nModulo 3 - Estudos de caso\nModulo 4 - Oficina orientada",
                'methodology' => 'Aulas dialogadas, dinamicas em grupo, resolucao de casos e orientacao para aplicacao em cenarios reais.',
                'evaluation' => 'Considera frequencia, participacao, desempenho nas atividades e aproveitamento geral demonstrado durante o curso.',
            ]],
        ];
    }
}
