<?php

declare(strict_types=1);

namespace Juampi92\PhpstanBaselineWarnings\Domain\Storage;

interface BaselineStorage
{
    /**
     * @throws \Exception if the file cannot be read
     */
    public function read(string $path): string;

    /**
     * @return bool Whether the file exists
     */
    public function exists(string $path): bool;
}
