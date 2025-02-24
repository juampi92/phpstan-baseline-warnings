<?php

declare(strict_types=1);

namespace Juampi92\PhpstanBaselineWarnings\Application;

use Juampi92\PhpstanBaselineWarnings\Domain\ValueObject\FileWarnings;
use Symfony\Component\Console\Output\OutputInterface;

final class WarningsOutputFormatter
{
    /**
     * @param  array<array-key, FileWarnings>  $warnings
     */
    public function output(array $warnings, string $format, OutputInterface $output): void
    {
        if ($format === 'github') {
            foreach ($warnings as $fileWarnings) {
                foreach ($fileWarnings->getWarnings() as $warning) {
                    $message = sprintf(
                        '::warning file=%s,line=0,title=%s::Found %d occurrences of this error skipped in the baseline.',
                        $warning->path,
                        $warning->identifier ?? $warning->message,
                        $warning->count
                    );

                    $output->writeln($message);
                }
            }
        }
    }
}
