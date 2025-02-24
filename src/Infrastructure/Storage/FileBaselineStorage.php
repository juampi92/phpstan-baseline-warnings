<?php

declare(strict_types=1);

namespace Juampi92\PhpstanBaselineWarnings\Infrastructure\Storage;

use Juampi92\PhpstanBaselineWarnings\Domain\Storage\BaselineStorage;

final class FileBaselineStorage implements BaselineStorage
{
    public function read(string $path): string
    {
        if (! $this->exists($path)) {
            throw new \Exception("Could not read baseline file at: {$path}");
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new \Exception("Could not read baseline file at: {$path}");
        }

        return $content;
    }

    public function exists(string $path): bool
    {
        return file_exists($path);
    }
}
