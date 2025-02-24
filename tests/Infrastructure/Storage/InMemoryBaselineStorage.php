<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Storage;

use Juampi92\PhpstanBaselineWarnings\Domain\Storage\BaselineStorage;

final class InMemoryBaselineStorage implements BaselineStorage
{
    /** @var array<string, string> */
    private array $files = [];

    public function write(string $path, string $content): void
    {
        $this->files[$path] = $content;
    }

    public function read(string $path): string
    {
        if (! $this->exists($path)) {
            throw new \Exception("Could not read baseline file at: {$path}");
        }

        return $this->files[$path];
    }

    public function exists(string $path): bool
    {
        return isset($this->files[$path]);
    }
}
