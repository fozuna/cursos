<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\Logger;
use InvalidArgumentException;
use Shuchkin\SimpleXLSX;

final class StudentImportService
{
    private const REQUIRED_COLUMNS = [
        'nome do aluno' => 'full_name',
        'curso' => 'course_name',
        'carga horaria' => 'workload_hours',
        'data de conclusao' => 'completion_date',
        'codigo do certificado' => 'certificate_code',
        'instrutor' => 'instructor_name',
        'instituicao' => 'institution_name',
    ];

    public function fromUpload(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || empty($file['tmp_name'])) {
            throw new InvalidArgumentException('Selecione um arquivo CSV ou XLSX valido.');
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new InvalidArgumentException('O upload do arquivo nao foi reconhecido pelo servidor.');
        }

        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, ['csv', 'xlsx'], true)) {
            throw new InvalidArgumentException('Formato nao suportado. Utilize CSV ou XLSX.');
        }

        $size = (int) ($file['size'] ?? 0);

        if ($size <= 0 || $size > 5 * 1024 * 1024) {
            throw new InvalidArgumentException('O arquivo deve ter ate 5 MB e conter dados validos.');
        }

        $targetFile = config('app.upload_path') . '/' . uniqid('import_', true) . '.' . $extension;
        ensure_directory(dirname($targetFile));

        if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
            throw new InvalidArgumentException('Nao foi possivel salvar o arquivo enviado no servidor.');
        }

        $rows = $this->parseSpreadsheet($targetFile);

        Logger::info('participants.import.processed', [
            'original_name' => $file['name'] ?? null,
            'stored_name' => basename($targetFile),
            'extension' => $extension,
            'rows' => count($rows),
            'size' => $size,
        ]);

        return [
            'original_name' => $file['name'] ?? null,
            'source_file' => basename($targetFile),
            'source_type' => $extension,
            'row_count' => count($rows),
            'rows' => $rows,
        ];
    }

    public function fromManual(array $payload): array
    {
        $row = $this->normalizeRow($payload);

        Logger::info('participants.manual.processed', [
            'student_name' => $row['full_name'],
            'course_name' => $row['course_name'],
        ]);

        return [
            'source_file' => null,
            'original_name' => null,
            'source_type' => 'manual',
            'row_count' => 1,
            'rows' => [$row],
        ];
    }

    private function parseSpreadsheet(string $filePath): array
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $rows = $extension === 'csv'
            ? $this->parseCsv($filePath)
            : $this->parseXlsx($filePath);

        if ($rows === []) {
            throw new InvalidArgumentException('A planilha enviada esta vazia.');
        }

        $headerRow = array_shift($rows);
        $mapping = $this->mapHeader($headerRow ?: []);
        $normalized = [];

        foreach ($rows as $row) {
            $payload = [];

            foreach ($mapping as $column => $field) {
                $payload[$field] = trim((string) ($row[$column] ?? ''));
            }

            if (implode('', $payload) === '') {
                continue;
            }

            $normalized[] = $this->normalizeRow($payload);
        }

        if ($normalized === []) {
            throw new InvalidArgumentException('Nenhum aluno valido foi encontrado no arquivo.');
        }

        return $normalized;
    }

    private function parseCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            throw new InvalidArgumentException('Nao foi possivel abrir o arquivo CSV enviado.');
        }

        $rows = [];

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) === 1) {
                $row = str_getcsv((string) $row[0], ',');
            }

            if ($rows === [] && isset($row[0])) {
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $row[0]) ?: (string) $row[0];
            }

            $rows[] = array_values($row);
        }

        fclose($handle);

        return $rows;
    }

    private function parseXlsx(string $filePath): array
    {
        $xlsx = SimpleXLSX::parse($filePath);

        if ($xlsx === false) {
            throw new InvalidArgumentException('Falha ao ler o arquivo XLSX enviado.');
        }

        return $xlsx->rows();
    }

    private function mapHeader(array $headerRow): array
    {
        $mapping = [];

        foreach ($headerRow as $column => $label) {
            $normalizedLabel = $this->normalizeHeader((string) $label);

            if (isset(self::REQUIRED_COLUMNS[$normalizedLabel])) {
                $mapping[$column] = self::REQUIRED_COLUMNS[$normalizedLabel];
            }
        }

        $missing = array_diff(self::REQUIRED_COLUMNS, $mapping);

        if ($missing !== []) {
            throw new InvalidArgumentException(
                'Cabecalho invalido. Campos obrigatorios: ' . implode(', ', array_keys(self::REQUIRED_COLUMNS))
            );
        }

        return $mapping;
    }

    private function normalizeRow(array $row): array
    {
        $payload = [
            'full_name' => trim((string) ($row['full_name'] ?? $row['nome'] ?? '')),
            'course_name' => trim((string) ($row['course_name'] ?? '')),
            'workload_hours' => (int) ($row['workload_hours'] ?? 0),
            'completion_date' => trim((string) ($row['completion_date'] ?? '')),
            'certificate_code' => trim((string) ($row['certificate_code'] ?? '')),
            'instructor_name' => trim((string) ($row['instructor_name'] ?? '')),
            'institution_name' => trim((string) ($row['institution_name'] ?? '')),
            'email' => trim((string) ($row['email'] ?? '')) ?: null,
            'document_number' => trim((string) ($row['document_number'] ?? '')) ?: null,
        ];

        foreach (['full_name', 'course_name', 'completion_date', 'instructor_name', 'institution_name'] as $field) {
            if ($payload[$field] === '') {
                throw new InvalidArgumentException(sprintf('Campo obrigatorio ausente: %s.', $field));
            }
        }

        if ($payload['workload_hours'] <= 0) {
            throw new InvalidArgumentException('A carga horaria deve ser maior que zero.');
        }

        return $payload;
    }

    private function normalizeHeader(string $header): string
    {
        $header = trim(mb_strtolower($header));
        $header = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $header) ?: $header;

        return preg_replace('/\s+/', ' ', $header) ?: '';
    }
}
