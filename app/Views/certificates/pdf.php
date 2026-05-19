<?php
$settings = json_decode((string) ($template['settings_json'] ?? '{}'), true) ?: [];
$accentColor = $settings['accentColor'] ?? '#1d4ed8';
$title = $settings['title'] ?? 'Certificado de Conclusao';
$subtitle = $settings['subtitle'] ?? 'Reconhecimento oficial de participacao e aproveitamento';
$footer = $settings['footer'] ?? 'Documento validavel online.';
$logoDataUri = public_asset_data_uri((string) config('app.institution.logo_path'));
$institutionCnpj = (string) config('app.institution.cnpj');
$formattedCompletionDate = format_date_br((string) $certificate['completion_date']);
$programSections = $certificate['program_sections'] ?? [];
$frontDensityScore = mb_strlen((string) $certificate['student_name'])
    + mb_strlen((string) $certificate['course_name'])
    + mb_strlen((string) $certificate['instructor_name'])
    + mb_strlen((string) $certificate['institution_name'])
    + mb_strlen((string) $certificate['validation_url']);
$frontCompactClass = $frontDensityScore >= 220 ? 'compact' : '';
$backDensityScore = mb_strlen((string) $certificate['student_name'])
    + mb_strlen((string) $certificate['course_name'])
    + array_sum(array_map('mb_strlen', $programSections));
$backCompactClass = $backDensityScore >= 420 ? 'compact' : '';

if ($programSections === [] && !empty($certificate['program_content'])) {
    $programSections = [
        'syllabus' => (string) $certificate['program_content'],
        'objectives' => '',
        'modules' => '',
        'methodology' => '',
        'evaluation' => '',
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= e($title) ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 7mm;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: <?= e($pdf['default_font']) ?>;
            color: #0f172a;
            background: #eef2f7;
        }
        .sheet {
            page-break-after: always;
        }
        .sheet:last-child {
            page-break-after: auto;
        }
        .page-shell {
            position: relative;
            overflow: hidden;
            border-radius: 7mm;
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(243, 247, 252, 0.98) 100%);
            border: 0.35mm solid rgba(15, 23, 42, 0.10);
        }
        .page-shell.front-sheet {
            background:
                radial-gradient(circle at 20% 16%, rgba(148, 163, 184, 0.10), transparent 22%),
                radial-gradient(circle at 82% 18%, rgba(30, 64, 175, 0.08), transparent 24%),
                linear-gradient(135deg, #ffffff 0%, #f6f8fb 45%, #eef3f8 100%);
        }
        .page-shell.back-sheet {
            background:
                radial-gradient(circle at 88% 12%, rgba(30, 64, 175, 0.07), transparent 24%),
                radial-gradient(circle at 12% 84%, rgba(100, 116, 139, 0.08), transparent 24%),
                linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #f1f5f9 100%);
        }
        .page-border {
            position: absolute;
            border-radius: 6mm;
        }
        .border-outer {
            top: 4mm;
            right: 4mm;
            bottom: 4mm;
            left: 4mm;
            border: 0.28mm solid rgba(15, 23, 42, 0.14);
        }
        .border-inner {
            top: 7mm;
            right: 7mm;
            bottom: 7mm;
            left: 7mm;
            border: 0.18mm solid rgba(148, 163, 184, 0.38);
        }
        .corner {
            position: absolute;
            width: 18mm;
            height: 18mm;
            border-color: rgba(15, 23, 42, 0.20);
        }
        .corner.tl {
            top: 7mm;
            left: 7mm;
            border-top: 0.45mm solid rgba(15, 23, 42, 0.18);
            border-left: 0.45mm solid rgba(15, 23, 42, 0.18);
            border-top-left-radius: 5mm;
        }
        .corner.tr {
            top: 7mm;
            right: 7mm;
            border-top: 0.45mm solid rgba(15, 23, 42, 0.18);
            border-right: 0.45mm solid rgba(15, 23, 42, 0.18);
            border-top-right-radius: 5mm;
        }
        .corner.bl {
            bottom: 7mm;
            left: 7mm;
            border-bottom: 0.45mm solid rgba(15, 23, 42, 0.18);
            border-left: 0.45mm solid rgba(15, 23, 42, 0.18);
            border-bottom-left-radius: 5mm;
        }
        .corner.br {
            bottom: 7mm;
            right: 7mm;
            border-bottom: 0.45mm solid rgba(15, 23, 42, 0.18);
            border-right: 0.45mm solid rgba(15, 23, 42, 0.18);
            border-bottom-right-radius: 5mm;
        }
        .page-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 86mm;
            height: 86mm;
            margin-top: -43mm;
            margin-left: -43mm;
            border: 0.28mm solid rgba(148, 163, 184, 0.12);
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.18);
            opacity: 0.35;
        }
        .page-watermark:before {
            content: '';
            position: absolute;
            top: 6mm;
            right: 6mm;
            bottom: 6mm;
            left: 6mm;
            border: 0.18mm solid rgba(148, 163, 184, 0.18);
            border-radius: 50%;
        }
        .watermark-copy {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 62mm;
            margin-top: -5mm;
            margin-left: -31mm;
            text-align: center;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 15pt;
            letter-spacing: 0.20em;
            text-transform: uppercase;
            color: rgba(71, 85, 105, 0.14);
        }
        .page-top-band {
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            height: 10mm;
            background: linear-gradient(90deg, rgba(15, 23, 42, 0.96) 0%, <?= e($accentColor) ?> 52%, rgba(15, 23, 42, 0.96) 100%);
        }
        .page-bottom-band {
            position: absolute;
            right: 0;
            bottom: 0;
            left: 0;
            height: 4mm;
            background: linear-gradient(90deg, rgba(15, 23, 42, 0.88) 0%, rgba(148, 163, 184, 0.18) 50%, rgba(15, 23, 42, 0.88) 100%);
        }
        .sheet-inner {
            position: relative;
            z-index: 2;
            padding: 14mm 13mm 9mm;
        }
        .top {
            text-align: center;
        }
        .eyebrow-row {
            width: 100%;
            margin-bottom: 3.2mm;
            overflow: hidden;
        }
        .eyebrow {
            float: left;
            font-size: 7pt;
            letter-spacing: 0.34em;
            text-transform: uppercase;
            color: #cbd5e1;
        }
        .eyebrow-code {
            float: right;
            font-size: 7pt;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #dbe4f0;
        }
        .logo-wrap {
            height: 15mm;
            text-align: center;
            margin-bottom: 2.5mm;
        }
        .logo-wrap img {
            max-width: 64mm;
            max-height: 15mm;
            width: auto;
            height: auto;
        }
        .title-wrap {
            width: 100%;
        }
        .title-rule {
            width: 30mm;
            height: 0.35mm;
            margin: 0 auto 2mm;
            background: linear-gradient(90deg, rgba(148, 163, 184, 0.10) 0%, rgba(15, 23, 42, 0.36) 50%, rgba(148, 163, 184, 0.10) 100%);
        }
        .title {
            margin: 0 0 1.3mm;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 23pt;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #0f172a;
        }
        .subtitle {
            width: 72%;
            margin: 0 auto;
            font-size: 8.6pt;
            line-height: 1.35;
            color: #475569;
        }
        .front-body {
            text-align: center;
            padding: 4.5mm 3mm 0;
        }
        .center-label {
            font-size: 8pt;
            letter-spacing: 0.50em;
            text-transform: uppercase;
            color: #64748b;
        }
        .student-panel {
            width: 78%;
            margin: 3mm auto 3mm;
        }
        .student {
            margin: 0 0 1.6mm;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 25pt;
            font-weight: 700;
            line-height: 1.1;
            color: #020617;
        }
        .student-underline {
            width: 62%;
            height: 0.45mm;
            margin: 0 auto;
            background: linear-gradient(90deg, rgba(148, 163, 184, 0.10) 0%, <?= e($accentColor) ?> 50%, rgba(148, 163, 184, 0.10) 100%);
        }
        .description-card {
            width: 92%;
            margin: 0 auto 3mm;
            padding: 3mm 4mm;
            border: 0.22mm solid rgba(148, 163, 184, 0.18);
            border-radius: 4mm;
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.94) 0%, rgba(248, 250, 252, 0.86) 100%);
        }
        .description {
            width: 100%;
            margin: 0;
            font-size: 9.4pt;
            line-height: 1.46;
            color: #334155;
        }
        .description strong {
            color: #0f172a;
        }
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 2mm;
        }
        .bottom-table td {
            vertical-align: top;
        }
        .signatures-col {
            width: 76%;
            padding-right: 5mm;
        }
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .signatures-table td {
            width: 50%;
            vertical-align: top;
            padding: 0 6mm 0 0;
        }
        .signatures-table td:last-child {
            padding-right: 0;
        }
        .signature-card {
            min-height: 21mm;
            padding: 3mm 3.2mm 2.4mm;
            border: 0.22mm solid rgba(148, 163, 184, 0.20);
            border-radius: 4mm;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.94) 0%, rgba(248, 250, 252, 0.88) 100%);
        }
        .line {
            width: 48mm;
            border-top: 0.26mm solid rgba(15, 23, 42, 0.28);
            margin-bottom: 2mm;
        }
        .signature-name {
            font-size: 8.4pt;
            font-weight: 700;
            line-height: 1.32;
            word-break: break-word;
            color: #0f172a;
        }
        .signature-role {
            font-size: 6.2pt;
            letter-spacing: 0.24em;
            color: #64748b;
            text-transform: uppercase;
            line-height: 1.4;
            padding-top: 1.1mm;
        }
        .institution-meta {
            padding-top: 1.2mm;
            font-size: 6.3pt;
            color: #64748b;
        }
        .code {
            margin-top: 2mm;
            padding: 1.8mm 2.3mm;
            font-size: 7.3pt;
            letter-spacing: 0.05em;
            color: #475569;
            border-left: 0.55mm solid <?= e($accentColor) ?>;
            background: rgba(255, 255, 255, 0.72);
            text-align: left;
        }
        .code strong {
            color: #0f172a;
        }
        .qr-col {
            width: 24%;
        }
        .qr-card {
            width: 100%;
            padding: 2.2mm 2.1mm 1.8mm;
            border-radius: 4.5mm;
            border: 0.25mm solid rgba(15, 23, 42, 0.12);
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.96) 0%, rgba(241, 245, 249, 0.88) 100%);
            text-align: center;
        }
        .qr-title {
            font-size: 6.1pt;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 1.2mm;
        }
        .qr-card img {
            width: 16mm;
            height: 16mm;
            display: block;
            margin: 0 auto 1mm;
        }
        .qr-card small {
            display: block;
            font-size: 5.6pt;
            line-height: 1.25;
            color: #64748b;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .front-footer-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .front-footer-table td {
            vertical-align: middle;
        }
        .footer-info {
            width: 36%;
        }
        .footer-middle {
            width: 42%;
            text-align: center;
            padding: 0 3mm;
        }
        .footer-qr {
            width: 22%;
            text-align: right;
        }
        .footer-note {
            padding: 1.8mm 2.2mm;
            border-top: 0.18mm solid rgba(148, 163, 184, 0.35);
        }
        .validation-link {
            font-size: 5.8pt;
            color: #475569;
            line-height: 1.2;
            word-break: break-word;
            text-align: center;
        }
        .footer {
            font-size: 6.3pt;
            color: #64748b;
            text-align: left;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .seal-badge {
            display: inline-block;
            min-width: 24mm;
            padding: 1.7mm 1.8mm;
            text-align: center;
            border: 0.22mm solid rgba(15, 23, 42, 0.16);
            border-radius: 50mm;
            background: rgba(255, 255, 255, 0.84);
        }
        .seal-badge strong {
            display: block;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 8.1pt;
            letter-spacing: 0.08em;
            color: #0f172a;
        }
        .seal-badge span {
            display: block;
            font-size: 5.2pt;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #64748b;
        }
        .back-body {
            padding: 5mm 1mm 0;
        }
        .back-title {
            margin: 0;
            text-align: center;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 18pt;
            color: #0f172a;
            letter-spacing: 0.04em;
        }
        .back-subtitle {
            width: 78%;
            margin: 1.8mm auto 4mm;
            text-align: center;
            font-size: 8.1pt;
            line-height: 1.32;
            color: #64748b;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 3mm;
            border-collapse: separate;
            border-spacing: 2mm 0;
        }
        .meta-table td {
            width: 33.33%;
            padding: 0;
            background: transparent;
            border: none;
        }
        .meta-card {
            min-height: 17mm;
            padding: 2.6mm 3mm;
            border: 0.22mm solid rgba(148, 163, 184, 0.18);
            border-radius: 4mm;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.96) 0%, rgba(248, 250, 252, 0.88) 100%);
        }
        .meta-table .label {
            display: block;
            font-size: 6pt;
            text-transform: uppercase;
            letter-spacing: 0.22em;
            color: #64748b;
            margin-bottom: 0.7mm;
        }
        .meta-table .value {
            display: block;
            font-size: 8pt;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.24;
        }
        .sections-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 2mm 2mm;
        }
        .sections-table td {
            padding: 0;
            vertical-align: top;
        }
        .section {
            margin: 0;
            padding: 3mm 3.2mm 2.8mm;
            border: 0.22mm solid rgba(148, 163, 184, 0.18);
            border-radius: 4mm;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.96) 0%, rgba(247, 250, 252, 0.90) 100%);
        }
        .section-title {
            margin: 0 0 1mm;
            font-size: 8.6pt;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: <?= e($accentColor) ?>;
            font-family: Georgia, "Times New Roman", serif;
        }
        .section-divider {
            width: 11mm;
            height: 0.35mm;
            margin-bottom: 1mm;
            background: linear-gradient(90deg, <?= e($accentColor) ?> 0%, rgba(148, 163, 184, 0.15) 100%);
        }
        .section-body {
            font-size: 7.8pt;
            line-height: 1.34;
            color: #334155;
            white-space: pre-line;
        }
        .section-body strong {
            color: #0f172a;
        }
        .top.compact .eyebrow-row {
            margin-bottom: 2mm;
        }
        .top.compact .eyebrow,
        .top.compact .eyebrow-code {
            font-size: 5.6pt;
        }
        .top.compact .logo-wrap {
            height: 12mm;
            margin-bottom: 1.8mm;
        }
        .top.compact .logo-wrap img {
            max-width: 56mm;
            max-height: 12mm;
        }
        .top.compact .title-rule {
            width: 22mm;
            margin-bottom: 1.6mm;
        }
        .top.compact .title {
            font-size: 18pt;
            margin-bottom: 1.2mm;
        }
        .top.compact .subtitle {
            width: 88%;
            font-size: 7.2pt;
            line-height: 1.22;
        }
        .front-body.compact {
            padding-top: 3mm;
            padding-bottom: 0;
        }
        .front-body.compact .center-label {
            font-size: 6.2pt;
            letter-spacing: 0.32em;
        }
        .front-body.compact .student-panel {
            width: 92%;
            margin: 1.8mm auto 1.8mm;
        }
        .front-body.compact .student {
            font-size: 18pt;
            margin-bottom: 1mm;
            line-height: 1.02;
        }
        .front-body.compact .student-underline {
            width: 52%;
            height: 0.3mm;
        }
        .front-body.compact .description-card {
            width: 97%;
            margin-bottom: 1.8mm;
            padding: 2mm 2.6mm;
        }
        .front-body.compact .description {
            font-size: 6.4pt;
            line-height: 1.16;
        }
        .front-body.compact .bottom-table {
            margin-bottom: 1.1mm;
        }
        .front-body.compact .signatures-col {
            padding-right: 3mm;
        }
        .front-body.compact .signatures-table td {
            padding-right: 3mm;
        }
        .front-body.compact .signature-card {
            min-height: 16mm;
            padding: 2mm 2.2mm 1.8mm;
        }
        .front-body.compact .line {
            width: 34mm;
            margin-bottom: 0.8mm;
        }
        .front-body.compact .signature-name {
            font-size: 6.2pt;
            line-height: 1.08;
        }
        .front-body.compact .signature-role,
        .front-body.compact .institution-meta,
        .front-body.compact .code,
        .front-body.compact .footer,
        .front-body.compact .validation-link,
        .front-body.compact .qr-card small,
        .front-body.compact .qr-title,
        .front-body.compact .seal-badge span {
            font-size: 4.8pt;
            line-height: 1.08;
        }
        .front-body.compact .code {
            margin-top: 1.2mm;
            padding: 1.3mm 1.6mm;
        }
        .front-body.compact .qr-card {
            padding: 1.3mm 1mm;
            border-radius: 3mm;
        }
        .front-body.compact .qr-card img {
            width: 10mm;
            height: 10mm;
            margin-bottom: 0.6mm;
        }
        .front-body.compact .footer-note {
            padding: 1.1mm 1.4mm;
        }
        .front-body.compact .seal-badge {
            min-width: 17mm;
            padding: 1.1mm 1mm;
        }
        .front-body.compact .seal-badge strong {
            font-size: 6.2pt;
        }
        .back-body.compact {
            padding-top: 4mm;
        }
        .back-body.compact .back-title {
            font-size: 15pt;
        }
        .back-body.compact .back-subtitle {
            width: 92%;
            margin-bottom: 3mm;
            font-size: 6.7pt;
            line-height: 1.18;
        }
        .back-body.compact .meta-table {
            margin-bottom: 2mm;
            border-spacing: 1.6mm 0;
        }
        .back-body.compact .meta-card {
            min-height: 13mm;
            padding: 1.7mm 1.8mm;
        }
        .back-body.compact .meta-table .label {
            font-size: 5.2pt;
            margin-bottom: 0.5mm;
        }
        .back-body.compact .meta-table .value {
            font-size: 6.2pt;
            line-height: 1.12;
        }
        .back-body.compact .sections-table {
            border-spacing: 1.6mm 1.6mm;
        }
        .back-body.compact .section {
            padding: 2mm 2.1mm 1.8mm;
            border-radius: 3mm;
        }
        .back-body.compact .section-title {
            font-size: 7.2pt;
            margin-bottom: 0.7mm;
        }
        .back-body.compact .section-divider {
            width: 9mm;
            margin-bottom: 0.7mm;
        }
        .back-body.compact .section-body {
            font-size: 6.2pt;
            line-height: 1.14;
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="page-shell front-sheet">
            <div class="page-top-band"></div>
            <div class="page-bottom-band"></div>
            <div class="page-border border-outer"></div>
            <div class="page-border border-inner"></div>
            <div class="corner tl"></div>
            <div class="corner tr"></div>
            <div class="corner bl"></div>
            <div class="corner br"></div>
            <div class="page-watermark">
                <div class="watermark-copy"><?= e($certificate['institution_name']) ?></div>
            </div>
            <div class="sheet-inner">
            <div class="top <?= e($frontCompactClass) ?>">
                <div class="eyebrow-row">
                    <div class="eyebrow">Executive Education</div>
                    <div class="eyebrow-code"><?= e($certificate['certificate_code']) ?></div>
                </div>
                <?php if ($logoDataUri !== null): ?>
                    <div class="logo-wrap">
                        <img src="<?= e($logoDataUri) ?>" alt="Logo da instituicao">
                    </div>
                <?php endif; ?>
                <div class="title-wrap">
                    <div class="title-rule"></div>
                    <div class="title"><?= e($title) ?></div>
                    <div class="subtitle"><?= e($subtitle) ?></div>
                </div>
            </div>

            <div class="front-body <?= e($frontCompactClass) ?>">
                <div class="center-label">Concedido a</div>
                <div class="student-panel">
                    <div class="student"><?= e($certificate['student_name']) ?></div>
                    <div class="student-underline"></div>
                </div>
                <div class="description-card">
                    <div class="description">
                        Pela conclusão do curso <strong><?= e($certificate['course_name']) ?></strong>,
                        com carga horária de <strong><?= e((string) $certificate['workload_hours']) ?> horas</strong>,
                        finalizado em <strong><?= e($formattedCompletionDate) ?></strong>,
                        ministrado por <strong><?= e($certificate['instructor_name']) ?></strong>.
                    </div>
                </div>

                <table class="bottom-table">
                    <tr>
                        <td class="signatures-col">
                            <table class="signatures-table">
                                <tr>
                                    <td>
                                        <div class="signature-card">
                                            <div class="line"></div>
                                            <div class="signature-name"><?= e($certificate['instructor_name']) ?></div>
                                            <div class="signature-role">Instrutor responsável</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="signature-card">
                                            <div class="line"></div>
                                            <div class="signature-name"><?= e($certificate['institution_name']) ?></div>
                                            <div class="signature-role">Instituição emissora</div>
                                            <div class="institution-meta">CNPJ: <?= e($institutionCnpj) ?></div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <div class="code">
                                Código do certificado: <strong><?= e($certificate['certificate_code']) ?></strong>
                            </div>
                        </td>
                        <td class="qr-col">
                            <div class="qr-card">
                                <div class="qr-title">Validação digital</div>
                                <img src="<?= e($qrCode) ?>" alt="QR Code de validacao">
                                <small>QR de validação</small>
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="front-footer-table">
                    <tr>
                        <td class="footer-info">
                            <div class="footer-note">
                                <div class="footer"><?= e($footer) ?></div>
                            </div>
                        </td>
                        <td class="footer-middle">
                            <div class="footer-note">
                                <div class="validation-link"><?= e($certificate['validation_url']) ?></div>
                            </div>
                        </td>
                        <td class="footer-qr">
                            <div class="seal-badge">
                                <span>Selo oficial</span>
                                <strong>Verified</strong>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        </div>
    </div>

    <div class="sheet">
        <div class="page-shell back-sheet">
            <div class="page-top-band"></div>
            <div class="page-bottom-band"></div>
            <div class="page-border border-outer"></div>
            <div class="page-border border-inner"></div>
            <div class="corner tl"></div>
            <div class="corner tr"></div>
            <div class="corner bl"></div>
            <div class="corner br"></div>
            <div class="page-watermark">
                <div class="watermark-copy">Program Content</div>
            </div>
            <div class="sheet-inner">
            <div class="top">
                <div class="eyebrow-row">
                    <div class="eyebrow">Academic Overview</div>
                    <div class="eyebrow-code"><?= e($certificate['certificate_code']) ?></div>
                </div>
                <h2 class="back-title">Conteudo Programático</h2>
                <p class="back-subtitle">Verso do certificado com ementa, objetivos, módulos, metodologia e critérios de avaliação.</p>
            </div>

            <div class="back-body <?= e($backCompactClass) ?>">
                <table class="meta-table">
                    <tr>
                        <td>
                            <div class="meta-card">
                                <span class="label">Participante</span>
                                <span class="value"><?= e($certificate['student_name']) ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="meta-card">
                                <span class="label">Curso</span>
                                <span class="value"><?= e($certificate['course_name']) ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="meta-card">
                                <span class="label">Código</span>
                                <span class="value"><?= e($certificate['certificate_code']) ?></span>
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="sections-table">
                    <tr>
                        <td colspan="2">
                            <div class="section">
                                <h3 class="section-title">Ementa detalhada</h3>
                                <div class="section-divider"></div>
                                <div class="section-body"><?= nl2br(e((string) ($programSections['syllabus'] ?? ''))) ?></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="section">
                                <h3 class="section-title">Objetivos do curso</h3>
                                <div class="section-divider"></div>
                                <div class="section-body"><?= nl2br(e((string) ($programSections['objectives'] ?? ''))) ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="section">
                                <h3 class="section-title">Metodologia utilizada</h3>
                                <div class="section-divider"></div>
                                <div class="section-body"><?= nl2br(e((string) ($programSections['methodology'] ?? ''))) ?></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="section">
                                <h3 class="section-title">Conteudo programático por módulo/aula</h3>
                                <div class="section-divider"></div>
                                <div class="section-body"><?= nl2br(e((string) ($programSections['modules'] ?? ''))) ?></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="section">
                                <h3 class="section-title">Critérios de avaliação</h3>
                                <div class="section-divider"></div>
                                <div class="section-body"><?= nl2br(e((string) ($programSections['evaluation'] ?? ''))) ?></div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        </div>
    </div>
</body>
</html>
