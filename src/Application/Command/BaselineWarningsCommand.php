<?php

declare(strict_types=1);

namespace Juampi92\PhpstanBaselineWarnings\Application\Command;

use Juampi92\PhpstanBaselineWarnings\Application\WarningsOutputFormatter;
use Juampi92\PhpstanBaselineWarnings\Domain\BaselineParser;
use Juampi92\PhpstanBaselineWarnings\Domain\BaselineWarningsCorrelator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class BaselineWarningsCommand extends Command
{
    public function __construct(
        private readonly BaselineParser $baselineParser,
        private readonly BaselineWarningsCorrelator $baselineWarningsCorrelator,
        private readonly WarningsOutputFormatter $warningsOutputFormatter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate warnings from PHPStan baseline for changed files')
            ->addOption(
                'baseline-path',
                'b',
                InputOption::VALUE_REQUIRED,
                'Path to the baseline.neon file',
                'phpstan-baseline.neon'
            )
            ->addOption(
                'base-dir',
                null,
                InputOption::VALUE_OPTIONAL,
                'Path to the root from the baseline. Used to correct the baseline  (optional)'
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'Output format (github)',
                'github'
            )
            ->addArgument(
                'files',
                InputArgument::IS_ARRAY,
                'List of changed files to check against baseline'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $baselinePath = $input->getOption('baseline-path');
        $baseDir = $input->getOption('base-dir');
        $format = $input->getOption('format');
        /** @var array<int, string> $filesToCheck */
        $filesToCheck = $input->getArgument('files');

        try {
            $warnings = $this->baselineParser->parseBaseline($baselinePath, $baseDir);
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return Command::FAILURE;
        }

        $correlatedWarnings = $this->baselineWarningsCorrelator->correlate($warnings, $filesToCheck);

        $this->warningsOutputFormatter->output($correlatedWarnings, $format, $output);

        return Command::SUCCESS;
    }
}
