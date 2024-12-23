<?php
declare(strict_types=1);

namespace Phtmx\Tests;

use Phtmx\Finder;
use PHPUnit\Framework\TestCase;

class FinderTest extends TestCase
{
    public function testGetFiles()
    {
        $finder = new Finder();
        $files = $finder->getFiles(__DIR__ . '/data/routes');
        $this->assertIsArray($files);
    }
}
