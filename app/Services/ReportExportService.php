<?php

declare(strict_types=1);

namespace App\Services;

final class ReportExportService
{
    public function __construct(
        private readonly PdfGeneratorService $pdfGeneratorService = new PdfGeneratorService()
    ) {
    }

    /**
     * @param array<int, string> $headers
     * @param array<int, array<int, scalar|null>> $rows
     */
    public function exportCsv(string $baseName, array $headers, array $rows): array
    {
        $directory = storage_path('exports');
        ensure_directory($directory);

        $fileName = $this->sanitize($baseName) . '-' . date('Ymd-His') . '.csv';
        $filePath = $directory . DIRECTORY_SEPARATOR . $fileName;
        $handle = fopen($filePath, 'wb');

        if ($handle === false) {
            throw new \RuntimeException('Nao foi possivel criar o arquivo CSV.');
        }

        fputcsv($handle, $headers, ';');

        foreach ($rows as $row) {
            fputcsv($handle, array_map(static fn ($value) => (string) ($value ?? ''), $row), ';');
        }

        fclose($handle);

        return ['path' => $filePath, 'name' => $fileName];
    }

    /**
     * @param array<int, string> $headers
     * @param array<int, array<int, scalar|null>> $rows
     */
    public function exportPdf(string $title, array $headers, array $rows): array
    {
        $directory = storage_path('exports');
        ensure_directory($directory);

        $fileName = $this->sanitize($title) . '-' . date('Ymd-His') . '.pdf';
        $filePath = $directory . DIRECTORY_SEPARATOR . $fileName;
        $html = render('reports.table', [
            'title' => $title,
            'headers' => $headers,
            'rows' => $rows,
            'generatedAt' => date('d/m/Y H:i'),
        ], null);

        $this->pdfGeneratorService->generate($html, $filePath);

        return ['path' => $filePath, 'name' => $fileName];
    }

    private function sanitize(string $text): string
    {
        $text = mb_strtolower($text);
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?: 'relatorio';

        return trim($text, '-') ?: 'relatorio';
    }
}
