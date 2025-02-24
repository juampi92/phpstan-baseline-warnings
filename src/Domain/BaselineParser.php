<?php

declare(strict_types=1);

namespace Juampi92\PhpstanBaselineWarnings\Domain;

use Juampi92\PhpstanBaselineWarnings\Domain\ValueObject\BaselineWarning;

final class BaselineParser
{
    public function __construct(
        private readonly Storage\BaselineStorage $storage,
    ) {}

    /**
     * @return array<string, BaselineWarning>
     *
     * @throws \Exception
     */
    public function parseBaseline(string $baselinePath, ?string $baseDir): array
    {
        try {
            $content = $this->storage->read($baselinePath);

            return (new Baseline)->parse($content, $baseDir ?: null);
        } catch (\Exception $e) {
            throw new \Exception("Failed to parse baseline file: {$e->getMessage()}");
        }
    }
}
