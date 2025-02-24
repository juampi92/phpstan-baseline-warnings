<?php

declare(strict_types=1);

namespace Juampi92\PhpstanBaselineWarnings\Domain;

use Juampi92\PhpstanBaselineWarnings\Domain\ValueObject\BaselineWarning;
use Juampi92\PhpstanBaselineWarnings\Domain\ValueObject\FileWarnings;

final class BaselineWarningsCorrelator
{
    /**
     * @param  array<BaselineWarning>  $warnings
     * @param  array<int, string>  $filesToCheck
     * @return array<array-key, FileWarnings>
     */
    public function correlate(array $warnings, array $filesToCheck): array
    {
        $fileWarningsMap = [];

        foreach ($filesToCheck as $file) {
            $fileWarningsMap[$file] = new FileWarnings($file);
        }

        foreach ($warnings as $warning) {
            if (isset($fileWarningsMap[$warning->path])) {
                $fileWarningsMap[$warning->path]->addWarning($warning);
            }
        }

        // Only return FileWarnings that actually have warnings
        return array_filter($fileWarningsMap, fn (FileWarnings $fw) => $fw->hasWarnings());
    }
}
