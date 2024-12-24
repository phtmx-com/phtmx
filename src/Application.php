<?php
declare(strict_types=1);

namespace Phtmx;

use FastRoute\Dispatcher;
use Psr\Http\Message\ServerRequestInterface;

class Application
{
    private static ServerRequestInterface $request;

    public function __construct(
        public string $routeDirectory,
        public string $middlewareDirectory,
        public array  $config = []
    )
    {
    }

    public static function request(): ServerRequestInterface
    {
        return static::$request;
    }

    public function run(ServerRequestInterface $request): void
    {
        static::$request = $request;
        $dispatcher = $this->getDispatcher();
        $routeInfo = $dispatcher->dispatch($request->getMethod(), trim($request->getUri()->getPath(), '/'));
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                http_response_code(404);
                echo '404 - Not Found';
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                http_response_code(405);
                echo '405 - Method Not Allowed';
                break;
            case \FastRoute\Dispatcher::FOUND:
                try {
                    http_response_code(200);
                    $handler = $routeInfo[1];
                    $vars = $routeInfo[2];
                    foreach ($vars as $key => $value) {
                        static::$request = $request->withAttribute($key, $value);
                    }
                    $middlewares = $handler['middlewares'];
                    $routes = $handler['routes'];
                    foreach ($middlewares as $middleware) {
                        require $middleware;
                    }
                    RoutePipeline::run($routes, $request);
                } catch (\Exception $e) {
                    http_response_code(500);
                    echo '500 - Internal Server Error';
                }
                break;
        }
    }

    private function getDispatcher(): Dispatcher
    {
        return \FastRoute\cachedDispatcher(function (\FastRoute\RouteCollector $r) {
            $routes = $this->getRoutes();
            foreach ($routes as $route => $data) {
                $r->addRoute(
                    static::$request->getMethod(),
                    $route,
                    $data
                );
            }
        }, [
            'cacheFile' => $this->config['cacheFile'] ?? tmpfile(),
            'cacheDisabled' => $this->config['cacheDisabled'] ?? true,
        ]);
    }

    private function getRoutes(): array
    {
        $middlewares = $this->getMiddlewares();
        $finder = new Finder();
        $extension = 'php';
        $files = $finder->getFiles($this->routeDirectory, $extension);
        $routes = [];
        foreach ($files as $file) {
            $route = [];
            $callbacks = [];
            $middlewareCallbacks = [];
            $parts = explode('.', $file->getBasename('.' . $extension));
            $subPath = '';
            foreach ($parts as $part) {
                $subPath .= $part . '.';
                $callback = $file->getPath() . '/' . $subPath . $extension;
                if (isset($middlewares[$part])) {
                    $middlewareCallbacks[$part] = $middlewares[$part];
                }
                if (file_exists($callback)) {
                    $callbacks[] = $callback;
                }
                if ($part == 'index') {
                    $route[] = '';
                    continue;
                }
                if (str_starts_with($part, '_')) {
                    continue;
                }
                $route[] = $part;
            }
            if ($route) {
                $routes[implode('/', $route)] = [
                    'routes' => array_reverse($callbacks),
                    'middlewares' => $middlewareCallbacks,
                ];
            }
        }
        uksort($routes, function ($a, $b) {
            if ($a === '' || $b === '') {
                return $a === '' ? 1 : -1;
            }
            $aParts = substr_count($a, $b);
            $bParts = substr_count($b, $a);
            if ($aParts == $bParts) {
                return 0;
            }
            return $bParts - $aParts;
        });
        return $routes;
    }

    private function getMiddlewares(): array
    {
        $finder = new Finder();
        $extension = 'php';
        $files = $finder->getFiles($this->middlewareDirectory, $extension);
        $middlewares = [];
        foreach ($files as $file) {
            $middlewares[$file->getBasename('.' . $extension)] = $file->getPathname();
        }
        return $middlewares;
    }
}
