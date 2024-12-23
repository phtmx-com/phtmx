<?php
declare(strict_types=1);

namespace Phtmx;

class Application
{
    public function __construct(
        public string $routeDirectory,
        public string $middlewareDirectory,
    )
    {
    }

    public function run(): void
    {

    }

    public function getRoutes(): array
    {
        $finder = new Finder();
        $extension = 'php';
        $files = $finder->getFiles($this->routeDirectory, $extension);
        $routes = [];
        foreach ($files as $file) {
            dump($file->getBasename('.'.$extension));die;
        }
        return $routes;
    }

}