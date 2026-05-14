<?php

declare(strict_types=1);

namespace App\Support;

final class Csrf
{
    public static function token(string $key = 'default'): string
    {
        if (!isset($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = [];
        }

        if (empty($_SESSION['_csrf'][$key])) {
            $_SESSION['_csrf'][$key] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION['_csrf'][$key];
    }

    public static function validate(?string $token, string $key = 'default'): bool
    {
        $expected = $_SESSION['_csrf'][$key] ?? null;

        return is_string($expected) && is_string($token) && hash_equals($expected, $token);
    }
}
