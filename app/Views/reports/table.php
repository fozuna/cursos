<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 22px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 11px;
        }
        h1 {
            margin: 0 0 8px;
            font-size: 20px;
        }
        .meta {
            margin-bottom: 18px;
            color: #475569;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #e2e8f0;
            font-weight: 700;
        }
        tr:nth-child(even) td {
            background: #f8fafc;
        }
    </style>
</head>
<body>
    <h1><?= e($title) ?></h1>
    <p class="meta">Gerado em <?= e($generatedAt) ?></p>
    <table>
        <thead>
            <tr>
                <?php foreach ($headers as $header): ?>
                    <th><?= e($header) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <?php foreach ($row as $value): ?>
                        <td><?= e((string) ($value ?? '')) ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
