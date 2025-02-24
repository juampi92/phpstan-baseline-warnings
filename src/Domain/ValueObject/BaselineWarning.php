<?php

namespace Juampi92\PhpstanBaselineWarnings\Domain\ValueObject;

class BaselineWarning
{
    public function __construct(
        public readonly string $message,
        public readonly int $count,
        public readonly string $path,
        public readonly ?string $identifier = null,
    ) {}
}
