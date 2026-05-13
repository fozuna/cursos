<?php

declare(strict_types=1);

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

final class PdfGeneratorService
{
    public function generate(string $html, string $targetPath): void
    {
        ensure_directory(dirname($targetPath));

        $options = new Options();
        $options->set('defaultFont', config('app.pdf.default_font'));
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper(config('app.pdf.paper'), config('app.pdf.orientation'));
        $dompdf->render();

        file_put_contents($targetPath, $dompdf->output());
    }
}
