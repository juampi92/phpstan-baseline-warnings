<?php

declare(strict_types=1);

namespace Tests\Integration;

use Juampi92\PhpstanBaselineWarnings\Application\Command\BaselineWarningsCommand;
use Juampi92\PhpstanBaselineWarnings\Application\WarningsOutputFormatter;
use Juampi92\PhpstanBaselineWarnings\Domain\BaselineParser;
use Juampi92\PhpstanBaselineWarnings\Domain\BaselineWarningsCorrelator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Infrastructure\Storage\InMemoryBaselineStorage;

class BaselineWarningsCommandTest extends TestCase
{
    private CommandTester $commandTester;

    private InMemoryBaselineStorage $storage;

    protected function setUp(): void
    {
        $this->storage = new InMemoryBaselineStorage;
        $baselineParser = new BaselineParser($this->storage);
        $baselineWarningsCorrelator = new BaselineWarningsCorrelator;
        $warningsOutputFormatter = new WarningsOutputFormatter;

        $command = new BaselineWarningsCommand($baselineParser, $baselineWarningsCorrelator, $warningsOutputFormatter);
        $this->commandTester = new CommandTester($command);
    }

    public function test_command_succeeds_with_valid_baseline(): void
    {
        $baselineContent = <<<NEON
parameters:
    ignoreErrors:
        -
            message: '#Method App\\\\Service\\\\Example::doSomething\(\) has parameter \$param with no type specified.#'
            path: app/Service/Example.php
            count: 1
NEON;

        $baselinePath = '/test_baseline.neon';
        $this->storage->write($baselinePath, $baselineContent);

        $this->commandTester->execute([
            '--baseline-path' => $baselinePath,
            '--format' => 'github',
            'files' => ['app/Service/Example.php'],
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();

        $this->assertSame(0, $statusCode);
        $this->assertStringContainsString('app/Service/Example.php', $output);
        $this->assertStringContainsString('Method App\\\\Service\\\\Example: :doSomething\(\)', $output); // The space is included because it's gh formatting.
    }

    public function test_command_fails_with_invalid_baseline_path(): void
    {
        $this->commandTester->execute([
            '--baseline-path' => '/non/existent/baseline.neon',
            '--format' => 'github',
            'files' => ['some/file.php'],
        ]);

        $statusCode = $this->commandTester->getStatusCode();
        $output = $this->commandTester->getDisplay();

        $this->assertSame(1, $statusCode);
        $this->assertStringContainsString('Could not read', $output);
    }

    public function test_command_succeeds_with_no_ignore_errors(): void
    {
        $baselineContent = <<<'NEON'
parameters:
    level: 8
NEON;

        $baselinePath = '/test_baseline_empty.neon';
        $this->storage->write($baselinePath, $baselineContent);

        $this->commandTester->execute([
            '--baseline-path' => $baselinePath,
            '--format' => 'github',
            'files' => ['some/file.php'],
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();

        $this->assertSame(0, $statusCode, 'When there are no ignoreErrors section found in the baseline, the command should succeed');
        $this->assertEquals('', $output, 'Output should be empty');
    }

    public function test_command_only_shows_warnings_for_checked_files(): void
    {
        $baselineContent = <<<NEON
parameters:
    ignoreErrors:
        -
            message: '#Method App\\Service\\Example::doSomething\(\) has parameter \$param with no type specified.#'
            path: app/Service/Example.php
            count: 1
        -
            message: '#Method App\\Service\\Other::doSomething\(\) has parameter \$param with no type specified.#'
            path: app/Service/Other.php
            count: 1
NEON;

        $baselinePath = '/test_baseline_multiple.neon';
        $this->storage->write($baselinePath, $baselineContent);

        $this->commandTester->execute([
            '--baseline-path' => $baselinePath,
            '--format' => 'github',
            'files' => ['app/Service/Example.php'], // Only check Example.php
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();

        $this->assertSame(0, $statusCode);
        $this->assertStringContainsString('app/Service/Example.php', $output);
        $this->assertStringNotContainsString('app/Service/Other.php', $output);
    }
}
