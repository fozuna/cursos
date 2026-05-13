<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$output = $root . '/dist/hostinger/cursos.traxter.com.br';

deletePath($output);
ensureDir($output);

$directoriesToCopy = [
    'app',
    'config',
    'database',
    'routes',
    'vendor',
];

foreach ($directoriesToCopy as $directory) {
    $source = $root . '/' . $directory;

    if (!is_dir($source)) {
        continue;
    }

    copyDirectory($source, $output . '/' . $directory);
}

$filesToCopy = [
    'composer.json',
    'composer.lock',
    '.env.example',
    'README.md',
];

foreach ($filesToCopy as $file) {
    $source = $root . '/' . $file;

    if (is_file($source)) {
        copy($source, $output . '/' . $file);
    }
}

copyDirectory($root . '/public/assets', $output . '/assets');

ensureDir($output . '/uploads');
ensureDir($output . '/storage/certificados');
ensureDir($output . '/storage/logs');

if (is_file($root . '/.env')) {
    copy($root . '/.env', $output . '/.env');
}

file_put_contents($output . '/index.php', buildFrontController());
file_put_contents($output . '/.htaccess', buildHtaccess());

echo json_encode([
    'output' => $output,
    'env_copied' => is_file($output . '/.env'),
    'has_vendor' => is_dir($output . '/vendor'),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

function buildFrontController(): string
{
    return <<<'PHP'
<?php

declare(strict_types=1);

use App\Core\Environment;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Support\Logger;

session_start();

require __DIR__ . '/vendor/autoload.php';

Environment::load(__DIR__ . '/.env');
$appConfig = require __DIR__ . '/config/app.php';
date_default_timezone_set((string) $appConfig['timezone']);

$router = new Router();
require __DIR__ . '/routes/web.php';

try {
    $router->dispatch(Request::capture());
} catch (Throwable $throwable) {
    Logger::error('application.unhandled_exception', [
        'message' => $throwable->getMessage(),
        'trace' => $throwable->getTraceAsString(),
    ]);

    $message = config('app.debug') ? $throwable->getMessage() : 'Erro interno no sistema.';
    Response::html(render('partials.500', ['message' => $message]), 500);
}
PHP;
}

function buildHtaccess(): string
{
    return <<<'HTACCESS'
Options -Indexes
DirectoryIndex index.php

<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteRule ^(app|config|database|deploy|routes|tests|vendor|storage|uploads)(/|$) - [F,L,NC]
    RewriteRule ^(\.env|composer\.(json|lock)|phpunit\.xml|\.gitignore)$ - [F,L,NC]

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
</IfModule>
HTACCESS;
}

function copyDirectory(string $source, string $destination): void
{
    ensureDir($destination);

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $target = $destination . '/' . $iterator->getSubPathName();

        if ($item->isDir()) {
            ensureDir($target);
            continue;
        }

        ensureDir(dirname($target));
        copy($item->getPathname(), $target);
    }
}

function ensureDir(string $path): void
{
    if (!is_dir($path) && !mkdir($path, 0775, true) && !is_dir($path)) {
        throw new RuntimeException('Nao foi possivel criar o diretorio: ' . $path);
    }
}

function deletePath(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    if (is_file($path) || is_link($path)) {
        unlink($path);
        return;
    }

    $items = array_diff(scandir($path) ?: [], ['.', '..']);

    foreach ($items as $item) {
        deletePath($path . '/' . $item);
    }

    rmdir($path);
}
