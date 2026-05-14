<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function html(string $content, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=UTF-8');
        echo $content;
        exit;
    }

    public static function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function download(string $filePath, string $downloadName): never
    {
        if (!file_exists($filePath)) {
            self::html('Arquivo nao encontrado.', 404);
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($downloadName) . '"');
        header('Content-Length: ' . (string) filesize($filePath));
        readfile($filePath);
        exit;
    }

    public static function inlinePdf(string $filePath, string $displayName): never
    {
        if (!file_exists($filePath)) {
            self::html('Arquivo nao encontrado.', 404);
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($displayName) . '"');
        header('Content-Length: ' . (string) filesize($filePath));
        readfile($filePath);
        exit;
    }
}
