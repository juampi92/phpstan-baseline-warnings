<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use Juampi92\PhpstanBaselineWarnings\Application\WarningsOutputFormatter;
use Juampi92\PhpstanBaselineWarnings\Domain\ValueObject\BaselineWarning;
use Juampi92\PhpstanBaselineWarnings\Domain\ValueObject\FileWarnings;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

final class WarningsOutputFormatterTest extends TestCase
{
    private WarningsOutputFormatter $formatter;

    private BufferedOutput $output;

    protected function setUp(): void
    {
        $this->formatter = new WarningsOutputFormatter;
        $this->output = new BufferedOutput;
    }

    public function test_github_format_outputs_warnings_correctly(): void
    {
        // Arrange
        $warnings = [
            new FileWarnings('src/Example.php', [
                new BaselineWarning(
                    message: 'Method Example::test() has no return type specified.',
                    count: 2,
                    path: 'src/Example.php',
                    identifier: 'MissingReturnType'
                ),
                new BaselineWarning(
                    message: 'Parameter $param has no type specified.',
                    count: 1,
                    path: 'src/Example.php',
                    identifier: 'MissingParamType'
                ),
            ]),
        ];

        // Act
        $this->formatter->output($warnings, 'github', $this->output);

        // Assert
        $expected = implode("\n", [
            '::warning file=src/Example.php,line=0,title=MissingReturnType::Found 2 occurrences of this error skipped in the baseline.',
            '::warning file=src/Example.php,line=0,title=MissingParamType::Found 1 occurrences of this error skipped in the baseline.',
            '',
        ]);

        $this->assertEquals($expected, $this->output->fetch());
    }

    public function test_github_format_with_no_identifier(): void
    {
        // Arrange
        $warnings = [
            new FileWarnings('src/Example.php', [
                new BaselineWarning(
                    message: 'Some error without identifier',
                    count: 1,
                    path: 'src/Example.php',
                    identifier: null
                ),
            ]),
        ];

        // Act
        $this->formatter->output($warnings, 'github', $this->output);

        // Assert
        $expected = "::warning file=src/Example.php,line=0,title=Some error without identifier::Found 1 occurrences of this error skipped in the baseline.\n";
        $this->assertEquals($expected, $this->output->fetch());
    }
}
