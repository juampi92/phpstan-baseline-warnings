<?php

declare(strict_types=1);

namespace Juampi92\PhpstanBaselineWarnings;

use Juampi92\PhpstanBaselineWarnings\Domain\BaselineParser;
use Juampi92\PhpstanBaselineWarnings\Domain\WarningsOutputFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class BaselineWarningsCommand extends Command
{
    protected static $defaultName = 'phpstan-baseline-warnings';

    public function __construct(
        private readonly BaselineParser $baselineParser,
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
                'Path to the baseline.neon file'
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
        $format = $input->getOption('format');
        /** @var array<int, string> $filesToCheck */
        $filesToCheck = $input->getArgument('files');

        try {
            $baseline = $this->baselineParser->parseBaseline($baselinePath);
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return Command::FAILURE;
        }

        if (!isset($baseline['parameters']['ignoreErrors'])) {
            $output->writeln("<info>No ignoreErrors section found in baseline</info>");
            return Command::SUCCESS;
        }

        $ignoreErrors = $baseline['parameters']['ignoreErrors'];
        $warnings = $this->baselineParser->processIgnoreErrors($ignoreErrors, $filesToCheck);

        $this->warningsOutputFormatter->output($warnings, $format, $output);

        return Command::SUCCESS;
    }
}