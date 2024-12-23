<?php
declare(strict_types=1);

namespace Phtmx\Tests;

use Phtmx\Application;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{

    public function testRun(): void
    {
        $app = new Application(
            routeDirectory: __DIR__ . '/routes',
            middlewareDirectory: __DIR__ . '/middlewares',
        );
        $app->run();
    }

    public function testGetRoutes(): void
    {
        $app = new Application(
            routeDirectory: __DIR__ . '/data/routes',
            middlewareDirectory: __DIR__ . '/data/middlewares',
        );
        $routes = $app->getRoutes();
        dump($routes);die;
        $this->assertIsArray($routes);
    }
}
