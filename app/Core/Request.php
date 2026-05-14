<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function __construct(
        private readonly array $get,
        private readonly array $post,
        private readonly array $files,
        private readonly array $server
    ) {
    }

    public static function capture(): self
    {
        return new self($_GET, $_POST, $_FILES, $_SERVER);
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $scriptName = str_replace('\\', '/', $this->server['SCRIPT_NAME'] ?? '');
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        if ($basePath !== '' && $basePath !== '/' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath)) ?: '/';
        }

        if (str_starts_with($path, '/index.php')) {
            $path = substr($path, strlen('/index.php')) ?: '/';
        }

        $normalizedPath = '/' . trim($path, '/');

        return $normalizedPath === '//' ? '/' : $normalizedPath;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    public function files(): array
    {
        return $this->files;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function ip(): string
    {
        $forwardedFor = trim((string) ($this->server['HTTP_X_FORWARDED_FOR'] ?? ''));

        if ($forwardedFor !== '') {
            $parts = array_map('trim', explode(',', $forwardedFor));

            if ($parts !== [] && filter_var($parts[0], FILTER_VALIDATE_IP)) {
                return $parts[0];
            }
        }

        $remoteAddr = trim((string) ($this->server['REMOTE_ADDR'] ?? ''));

        return filter_var($remoteAddr, FILTER_VALIDATE_IP) ? $remoteAddr : '0.0.0.0';
    }

    public function userAgent(): ?string
    {
        $userAgent = trim((string) ($this->server['HTTP_USER_AGENT'] ?? ''));

        return $userAgent !== '' ? substr($userAgent, 0, 255) : null;
    }

    public function isSecure(): bool
    {
        $https = strtolower((string) ($this->server['HTTPS'] ?? ''));
        $forwardedProto = strtolower((string) ($this->server['HTTP_X_FORWARDED_PROTO'] ?? ''));
        $forwardedSsl = strtolower((string) ($this->server['HTTP_X_FORWARDED_SSL'] ?? ''));
        $serverPort = (string) ($this->server['SERVER_PORT'] ?? '');

        return in_array($https, ['on', '1'], true)
            || $forwardedProto === 'https'
            || $forwardedSsl === 'on'
            || $serverPort === '443';
    }
}
