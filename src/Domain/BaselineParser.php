<?php

declare(strict_types=1);

namespace Juampi92\PhpstanBaselineWarnings\Domain;

use Juampi92\PhpstanBaselineWarnings\Domain\ValueObject\BaselineWarning;

/**
 * BaselineParser is responsible for parsing PHPStan baseline files and extracting error information.
 * It handles both standard and identifier-based baseline entries, converting them into BaselineWarning objects.
 * The parser supports relative and absolute file paths, and can normalize them using an optional base directory.
 */
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
