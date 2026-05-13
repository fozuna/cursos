<?php

declare(strict_types=1);

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $basePath = dirname(__DIR__, 2);

        return $path === '' ? $basePath : $basePath . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('config')) {
    function config(?string $key = null, mixed $default = null): mixed
    {
        static $config = null;

        if ($config === null) {
            $config = [
                'app' => require base_path('config/app.php'),
                'database' => require base_path('config/database.php'),
            ];
        }

        if ($key === null) {
            return $config;
        }

        $segments = explode('.', $key);
        $value = $config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        $basePath = config('app.storage_path');

        return $path === '' ? $basePath : $basePath . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        $publicPath = rtrim((string) config('app.public_path'), '/\\');

        return $path === '' ? $publicPath : $publicPath . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return rtrim((string) config('app.base_url'), '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $baseUrl = rtrim((string) config('app.base_url'), '/');
        $normalizedPath = ltrim($path, '/');

        if ((bool) config('app.use_index')) {
            return $normalizedPath === ''
                ? $baseUrl . '/index.php'
                : $baseUrl . '/index.php/' . $normalizedPath;
        }

        return $normalizedPath === '' ? $baseUrl : $baseUrl . '/' . $normalizedPath;
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('render')) {
    function render(string $view, array $data = [], string $layout = 'layouts/app'): string
    {
        $viewFile = base_path('app/Views/' . str_replace('.', '/', $view) . '.php');
        $layoutFile = base_path('app/Views/' . str_replace('.', '/', $layout) . '.php');

        if (!file_exists($viewFile)) {
            throw new RuntimeException(sprintf('View "%s" nao encontrada.', $view));
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = (string) ob_get_clean();

        if ($layout === '') {
            return $content;
        }

        ob_start();
        require $layoutFile;

        return (string) ob_get_clean();
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): never
    {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('ensure_directory')) {
    function ensure_directory(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0775, true) && !is_dir($path)) {
            throw new RuntimeException(sprintf('Nao foi possivel criar o diretorio: %s', $path));
        }
    }
}

if (!function_exists('format_date_br')) {
    function format_date_br(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '';
        }

        $date = DateTime::createFromFormat('Y-m-d', $value) ?: new DateTime($value);

        return $date->format('d/m/Y');
    }
}

if (!function_exists('public_asset_data_uri')) {
    function public_asset_data_uri(string $relativePath): ?string
    {
        $absolutePath = public_path($relativePath);

        if (!is_file($absolutePath)) {
            return null;
        }

        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        if (in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true) && !extension_loaded('gd')) {
            return null;
        }

        $mimeType = mime_content_type($absolutePath) ?: 'application/octet-stream';
        $contents = file_get_contents($absolutePath);

        if ($contents === false) {
            return null;
        }

        return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
    }
}
