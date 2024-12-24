<?php
declare(strict_types=1);

namespace Phtmx;

use Psr\Http\Message\ServerRequestInterface;

class RoutePipeline
{
    private static string $response = '';

    public static function run(
        array                  $files,
        ServerRequestInterface $request
    ): void
    {
        $is_hx = (bool)$request->getHeader('HX-Request');
        foreach ($files as $file) {
            ob_start();
            require $file;
            static::$response .= ob_get_clean();
            if ($is_hx) {
                break;
            }
        }
        echo static::$response;
    }

    public static function outlet(): void
    {
        echo static::$response;
        self::$response = '';
    }
}
