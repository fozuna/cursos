<?php

declare(strict_types=1);

namespace App\Support;

final class Logger
{
    public static function info(string $event, array $context = []): void
    {
        self::write('info', $event, $context);
    }

    public static function warning(string $event, array $context = []): void
    {
        self::write('warning', $event, $context);
    }

    public static function error(string $event, array $context = []): void
    {
        self::write('error', $event, $context);
    }

    private static function write(string $level, string $event, array $context): void
    {
        $payload = [
            'timestamp' => date('c'),
            'level' => $level,
            'event' => $event,
            'context' => $context,
        ];

        $filePath = storage_path('logs/app.log');
        ensure_directory(dirname($filePath));
        file_put_contents(
            $filePath,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND
        );
    }
}
