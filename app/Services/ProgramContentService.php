<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

final class ProgramContentService
{
    private const MAX_CHARACTERS = 4200;
    private const MAX_ESTIMATED_LINES = 58;

    /**
     * @return array{
     *     content: string,
     *     sections: array{
     *         syllabus: string,
     *         objectives: string,
     *         modules: string,
     *         methodology: string,
     *         evaluation: string
     *     },
     *     character_count: int,
     *     estimated_lines: int
     * }
     */
    public function buildFromPayload(array $payload): array
    {
        return $this->buildFromSections([
            'syllabus' => (string) ($payload['program_syllabus'] ?? ''),
            'objectives' => (string) ($payload['program_objectives'] ?? ''),
            'modules' => (string) ($payload['program_modules'] ?? ''),
            'methodology' => (string) ($payload['program_methodology'] ?? ''),
            'evaluation' => (string) ($payload['program_evaluation'] ?? ''),
        ]);
    }

    /**
     * @param array<string, string> $sections
     * @return array{
     *     content: string,
     *     sections: array{
     *         syllabus: string,
     *         objectives: string,
     *         modules: string,
     *         methodology: string,
     *         evaluation: string
     *     },
     *     character_count: int,
     *     estimated_lines: int
     * }
     */
    public function buildFromSections(array $sections): array
    {
        $normalized = [
            'syllabus' => $this->normalizeText((string) ($sections['syllabus'] ?? '')),
            'objectives' => $this->normalizeText((string) ($sections['objectives'] ?? '')),
            'modules' => $this->normalizeText((string) ($sections['modules'] ?? '')),
            'methodology' => $this->normalizeText((string) ($sections['methodology'] ?? '')),
            'evaluation' => $this->normalizeText((string) ($sections['evaluation'] ?? '')),
        ];

        foreach ($this->labels() as $key => $label) {
            if ($normalized[$key] === '') {
                throw new InvalidArgumentException(sprintf('O campo "%s" do conteudo programatico e obrigatorio.', $label));
            }
        }

        $content = implode("\n\n", [
            "Ementa detalhada:\n" . $normalized['syllabus'],
            "Objetivos do curso:\n" . $normalized['objectives'],
            "Conteudo programatico por modulo/aula:\n" . $normalized['modules'],
            "Metodologia utilizada:\n" . $normalized['methodology'],
            "Criterios de avaliacao:\n" . $normalized['evaluation'],
        ]);
        $characterCount = mb_strlen($content);
        $estimatedLines = $this->estimateLineCount($normalized);

        if ($characterCount > self::MAX_CHARACTERS || $estimatedLines > self::MAX_ESTIMATED_LINES) {
            throw new InvalidArgumentException(
                'O conteudo programatico excede o espaco disponivel no verso do certificado. Resuma os textos antes da emissao.'
            );
        }

        return [
            'content' => $content,
            'sections' => $normalized,
            'character_count' => $characterCount,
            'estimated_lines' => $estimatedLines,
        ];
    }

    /**
     * @return array{
     *     syllabus: string,
     *     objectives: string,
     *     modules: string,
     *     methodology: string,
     *     evaluation: string
     * }
     */
    public function defaultSections(): array
    {
        return [
            'syllabus' => 'Visao ampla do curso, competencias desenvolvidas e aplicabilidade pratica no contexto corporativo.',
            'objectives' => 'Capacitar os participantes para aplicar os conceitos estudados com seguranca, criterio tecnico e visao de resultados.',
            'modules' => "Modulo 1 - Fundamentos\nModulo 2 - Aplicacao pratica\nModulo 3 - Estudos de caso e exercicios",
            'methodology' => 'Aulas expositivas dialogadas, demonstracoes praticas, atividades orientadas e estudos de caso.',
            'evaluation' => 'Participacao, desempenho nas atividades propostas e aproveitamento geral no curso.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [
            'syllabus' => 'Ementa detalhada',
            'objectives' => 'Objetivos do curso',
            'modules' => 'Conteudo programatico por modulo/aula',
            'methodology' => 'Metodologia utilizada',
            'evaluation' => 'Criterios de avaliacao',
        ];
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", trim($text));
        $lines = array_map(static fn (string $line): string => trim($line), explode("\n", $text));
        $lines = array_values(array_filter($lines, static fn (string $line): bool => $line !== ''));

        return implode("\n", $lines);
    }

    /**
     * @param array<string, string> $sections
     */
    private function estimateLineCount(array $sections): int
    {
        $lineCount = 0;

        foreach ($sections as $section) {
            $lineCount += 2;

            foreach (explode("\n", $section) as $line) {
                $line = trim($line);
                $lineCount += max(1, (int) ceil(mb_strlen($line) / 86));
            }
        }

        return $lineCount;
    }
}
