<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\ProgramContentService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ProgramContentServiceTest extends TestCase
{
    public function testBuildFromPayloadReturnsStructuredContent(): void
    {
        $service = new ProgramContentService();

        $result = $service->buildFromPayload([
            'program_syllabus' => 'Conceitos, terminologia e contexto do curso.',
            'program_objectives' => 'Aplicar os conceitos em cenarios reais.',
            'program_modules' => "Modulo 1 - Fundamentos\nModulo 2 - Pratica guiada",
            'program_methodology' => 'Aulas expositivas e laboratorio pratico.',
            'program_evaluation' => 'Participacao e estudo de caso final.',
        ]);

        self::assertSame('Conceitos, terminologia e contexto do curso.', $result['sections']['syllabus']);
        self::assertStringContainsString('Objetivos do curso:', $result['content']);
        self::assertGreaterThan(0, $result['estimated_lines']);
    }

    public function testBuildFromPayloadRequiresAllSections(): void
    {
        $service = new ProgramContentService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ementa detalhada');

        $service->buildFromPayload([
            'program_syllabus' => '',
            'program_objectives' => 'Objetivos',
            'program_modules' => 'Modulos',
            'program_methodology' => 'Metodologia',
            'program_evaluation' => 'Avaliacao',
        ]);
    }

    public function testBuildFromPayloadRejectsOversizedContent(): void
    {
        $service = new ProgramContentService();
        $longBlock = trim(str_repeat('Texto extenso para ocupar o verso do certificado. ', 120));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('excede o espaco disponivel');

        $service->buildFromPayload([
            'program_syllabus' => $longBlock,
            'program_objectives' => $longBlock,
            'program_modules' => $longBlock,
            'program_methodology' => $longBlock,
            'program_evaluation' => $longBlock,
        ]);
    }
}
