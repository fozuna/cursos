<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CertificateRepository;
use ZipStream\ZipStream;

final class ZipExportService
{
    public function __construct(
        private readonly CertificateRepository $certificateRepository = new CertificateRepository()
    ) {
    }

    public function exportBatch(int $batchId): ?string
    {
        $files = $this->certificateRepository->generatedFilesByBatch($batchId);

        if ($files === []) {
            return null;
        }

        ensure_directory(public_path('storage/certificados'));

        $zipName = sprintf('lote-certificados-%d.zip', $batchId);
        $absoluteZip = public_path('storage/certificados/' . $zipName);
        $relativeZip = 'storage/certificados/' . $zipName;

        $outputStream = fopen($absoluteZip, 'wb');

        if ($outputStream === false) {
            return null;
        }

        $zip = new ZipStream(
            outputName: $zipName,
            outputStream: $outputStream,
            sendHttpHeaders: false,
            enableZip64: true
        );

        foreach ($files as $file) {
            $absoluteFile = public_path($file['pdf_path']);

            if (file_exists($absoluteFile)) {
                $zip->addFile(
                    fileName: basename($absoluteFile),
                    data: (string) file_get_contents($absoluteFile)
                );
            }
        }

        $zip->finish();
        fclose($outputStream);

        return $relativeZip;
    }
}
