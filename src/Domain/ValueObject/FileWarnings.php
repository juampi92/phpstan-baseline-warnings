<?php

declare(strict_types=1);

namespace Juampi92\PhpstanBaselineWarnings\Domain\ValueObject;

final class FileWarnings
{
    /** @var array<array-key, BaselineWarning> */
    private array $warnings;

    public function __construct(
        public readonly string $file
    ) {
        $this->warnings = [];
    }

    public function addWarning(BaselineWarning $error): void
    {
        $this->warnings[] = $error;
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    /**
     * @return array<string, BaselineWarning>
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
