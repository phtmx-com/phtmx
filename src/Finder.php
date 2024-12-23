<?php
declare(strict_types=1);

namespace Phtmx;
class Finder
{
    /**
     * @param string $dir
     * @param string|null $extension
     * @return \SplFileInfo[]
     */
    public function getFiles(string $dir, ?string $extension = null): array
    {
        $files = [];
        $dir = rtrim($dir, '/');
        /** @var \SplFileInfo[] $iterator */
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir)
        );
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                if ($extension && $extension !== $file->getExtension()) {
                    continue;
                }
                $files[] = $file;
            }
        }
        return $files;
    }
}