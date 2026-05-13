<?php

declare(strict_types=1);

namespace App\Core;

use App\Support\Logger;
use Closure;
use RuntimeException;

final class Router
{
    /**
     * @var array<string, array<string, Closure|array{0: class-string, 1: string}>>
     */
    private array $routes = [];

    public function get(string $path, Closure|array $handler): void
    {
        $this->register('GET', $path, $handler);
    }

    public function post(string $path, Closure|array $handler): void
    {
        $this->register('POST', $path, $handler);
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();

        Logger::info('router.dispatch', [
            'method' => $method,
            'path' => $path,
        ]);

        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = '#^' . preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_-]*)\}#', '(?P<$1>[^/]+)', $route) . '$#';

            if (!preg_match($pattern, $path, $matches)) {
                continue;
            }

            $parameters = array_filter($matches, static fn (string|int $key): bool => is_string($key), ARRAY_FILTER_USE_KEY);

            if ($handler instanceof Closure) {
                $handler($request, $parameters);

                return;
            }

            [$className, $methodName] = $handler;
            $controller = new $className();

            if (!method_exists($controller, $methodName)) {
                throw new RuntimeException(sprintf('Metodo "%s" nao encontrado no controller "%s".', $methodName, $className));
            }

            $controller->{$methodName}($request, $parameters);

            return;
        }

        Logger::warning('router.not_found', [
            'method' => $method,
            'path' => $path,
            'registered_routes' => array_keys($this->routes[$method] ?? []),
        ]);

        Response::html(render('partials.404', ['path' => $path]), 404);
    }

    private function register(string $method, string $path, Closure|array $handler): void
    {
        $normalizedPath = '/' . trim($path, '/');
        $this->routes[$method][$normalizedPath === '/' ? '/' : $normalizedPath] = $handler;
    }
}
