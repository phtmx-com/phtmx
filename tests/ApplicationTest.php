<?php
declare(strict_types=1);

namespace Phtmx\Tests;

use Laminas\Diactoros\ServerRequestFactory;
use Phtmx\Application;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{

    public function testRun(): void
    {
        $app = new Application(
            routeDirectory: __DIR__ . '/data/routes',
            middlewareDirectory: __DIR__ . '/data/middlewares',
        );
        ob_start();
        $app->run(ServerRequestFactory::fromGlobals([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
        ]));
        $output = ob_get_clean();
        $this->assertEquals(
            '<html><body><h1>Hello World!</h1></body></html>',
            $output
        );
    }

}
