<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
App\Core\Environment::load(dirname(__DIR__) . '/.env');

$pdo = App\Core\Database::connection();
$statement = $pdo->query(
    "SELECT c.certificate_code, c.pdf_path, s.full_name
     FROM certificates c
     INNER JOIN students s ON s.id = c.student_id
     WHERE s.full_name IN ('Teste Sequencial A', 'Teste Sequencial B')
     ORDER BY c.id DESC
     LIMIT 2"
);

$rows = $statement->fetchAll(PDO::FETCH_ASSOC);
$result = [];

foreach ($rows as $row) {
    $pdfContents = '';
    $pageCount = null;

    if (!empty($row['pdf_path'])) {
        $pdfContents = file_get_contents(dirname(__DIR__) . '/public/' . $row['pdf_path']) ?: '';
    }

    if ($pdfContents !== '') {
        preg_match_all('/\/Count\s+(\d+)\b/', $pdfContents, $matches);
        $counts = array_map('intval', $matches[1] ?? []);
        $pageCount = $counts !== [] ? max($counts) : null;
    }

    $result[] = [
        'student_name' => $row['full_name'],
        'certificate_code' => $row['certificate_code'],
        'pdf_count' => $pageCount,
        'pdf_exists' => !empty($row['pdf_path']) && file_exists(dirname(__DIR__) . '/public/' . $row['pdf_path']),
    ];
}

echo json_encode([
    'certificates' => $result,
    'unique_codes' => count(array_unique(array_column($result, 'certificate_code'))) === count($result),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
