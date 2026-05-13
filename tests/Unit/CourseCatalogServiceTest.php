<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CourseCatalogService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CourseCatalogServiceTest extends TestCase
{
    public function testValidateAndNormalizeBuildsCoursePayload(): void
    {
        $service = new CourseCatalogService();

        $result = $service->validateAndNormalize([
            'name' => 'MBA Executivo em Gestao de Projetos',
            'workload_hours' => 80,
            'start_date' => '2026-01-10',
            'end_date' => '2026-02-10',
            'instructor_name' => 'Prof. Carlos Mendes',
            'institution_name' => 'TRAXTER Academy',
            'certificate_prefix' => 'TRX',
            'program_syllabus' => 'Visao geral do programa e fundamentos.',
            'program_objectives' => 'Capacitar gestores para atuacao estrategica.',
            'program_modules' => "Modulo 1\nModulo 2",
            'program_methodology' => 'Aulas ao vivo e estudos de caso.',
            'program_evaluation' => 'Participacao e projeto aplicado.',
            'is_active' => '1',
        ], 'TRAXTER Academy');

        self::assertSame('MBA Executivo em Gestao de Projetos', $result['name']);
        self::assertSame(80, $result['workload_hours']);
        self::assertSame('TRX', $result['certificate_prefix']);
        self::assertSame(1, $result['is_active']);
    }

    public function testValidateAndNormalizeRejectsInvalidPeriod(): void
    {
        $service = new CourseCatalogService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A data de inicio nao pode ser maior que a data de termino.');

        $service->validateAndNormalize([
            'name' => 'Curso invalido',
            'workload_hours' => 20,
            'start_date' => '2026-03-10',
            'end_date' => '2026-02-10',
            'instructor_name' => 'Instrutor',
            'institution_name' => 'Instituicao',
            'certificate_prefix' => 'CERT',
            'program_syllabus' => 'Ementa',
            'program_objectives' => 'Objetivos',
            'program_modules' => 'Modulo',
            'program_methodology' => 'Metodo',
            'program_evaluation' => 'Avaliacao',
            'is_active' => '1',
        ], 'Instituicao');
    }
}
