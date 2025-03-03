<?php

declare(strict_types=1);

namespace Juampi92\PhpstanBaselineWarnings\Application;

use Juampi92\PhpstanBaselineWarnings\Domain\ValueObject\BaselineWarning;
use Juampi92\PhpstanBaselineWarnings\Domain\ValueObject\FileWarnings;
use Symfony\Component\Console\Output\OutputInterface;

final class WarningsOutputFormatter
{
    /**
     * @param  non-empty-array<array-key, FileWarnings>  $fileWarningsCollection
     */
    public function output(array $fileWarningsCollection, string $format, OutputInterface $output): void
    {
        if ($format === 'github') {
            foreach ($fileWarningsCollection as $fileWarnings) {
                $message = $this->outputFileWarnings($fileWarnings);
                if ($message !== null) {
                    $output->writeln($message);
                }
            }
        }
    }

    private function outputFileWarnings(FileWarnings $fileWarnings): ?string
    {
        $warnings = $fileWarnings->getWarnings();
        if (empty($warnings)) {
            return null;
        }

        $filePath = $fileWarnings->file;
        $groupedWarnings = $this->groupWarningsByIdentifier($warnings);

        $totalWarnings = $this->countTotalWarnings($groupedWarnings);
        $totalOccurrences = $this->countTotalOccurrences($groupedWarnings);

        $message = $this->buildWarningsMessage($groupedWarnings, $totalWarnings, $totalOccurrences);
        $formattedMessage = $this->formatForGithub($message);

        return sprintf(
            '::warning file=%s,line=0,title=PHPStan baselined errors::%s',
            $filePath,
            $formattedMessage
        );
    }

    /**
     * @param  array<BaselineWarning>  $warnings
     * @return array<string, array{count: int, warning: BaselineWarning}>
     */
    private function groupWarningsByIdentifier(array $warnings): array
    {
        $groupedWarnings = [];

        foreach ($warnings as $warning) {
            $key = $warning->identifier ?? $warning->message;
            if (! isset($groupedWarnings[$key])) {
                $groupedWarnings[$key] = [
                    'count' => 0,
                    'warning' => $warning,
                ];
            }
            $groupedWarnings[$key]['count'] += $warning->count;
        }

        // Sort by count in descending order
        uasort($groupedWarnings, fn ($a, $b) => $b['count'] <=> $a['count']);

        return $groupedWarnings;
    }

    /**
     * @param  array<string, array{count: int, warning: BaselineWarning}>  $groupedWarnings
     */
    private function countTotalWarnings(array $groupedWarnings): int
    {
        return count($groupedWarnings);
    }

    /**
     * @param  array<string, array{count: int, warning: BaselineWarning}>  $groupedWarnings
     */
    private function countTotalOccurrences(array $groupedWarnings): int
    {
        return array_sum(array_column($groupedWarnings, 'count'));
    }

    /**
     * @param  array<string, array{count: int, warning: BaselineWarning}>  $groupedWarnings
     */
    private function buildWarningsMessage(array $groupedWarnings, int $totalWarnings, int $totalOccurrences): string
    {
        $message = sprintf('Found %d errors that occur a total of %d times:', $totalWarnings, $totalOccurrences);

        $displayCount = $totalWarnings <= 3 ? $totalWarnings : 2;
        $index = 0;

        foreach ($groupedWarnings as $identifier => $data) {
            if ($index >= $displayCount) {
                $remainingCount = $totalWarnings - $displayCount;
                if ($remainingCount > 0) {
                    $message .= sprintf("\n...%d more", $remainingCount);
                }
                break;
            }

            $message .= sprintf("\n- `%s` : %d times", $identifier, $data['count']);
            $index++;
        }

        return $message;
    }

    /**
     * Format a message to comply with GitHub Actions workflow command requirements
     * - Convert newlines to %0A
     * - Escape :: sequences to prevent breaking the workflow command format
     */
    private function formatForGithub(string $message): string
    {
        // Replace newlines with %0A for GitHub Actions
        $formatted = str_replace("\n", '%0A', $message);

        // Escape :: sequences to prevent breaking GitHub Actions workflow commands
        $formatted = str_replace('::', ': :', $formatted);

        return $formatted;
    }
}
