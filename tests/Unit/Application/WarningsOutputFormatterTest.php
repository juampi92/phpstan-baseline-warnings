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
        $expected = '::warning file=src/Example.php,line=0,title=PHPStan baselined errors::Found 2 errors that occur a total of 3 times:%0A- `MissingReturnType` : 2 times%0A- `MissingParamType` : 1 times'."\n";
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
        $expected = '::warning file=src/Example.php,line=0,title=PHPStan baselined errors::Found 1 errors that occur a total of 1 times:%0A- `Some error without identifier` : 1 times'."\n";
        $this->assertEquals($expected, $this->output->fetch());
    }

    public function test_github_format_truncates_when_more_than_three_errors(): void
    {
        // Arrange
        $warnings = [
            new FileWarnings('src/Example.php', [
                new BaselineWarning(
                    message: 'First error',
                    count: 5,
                    path: 'src/Example.php',
                    identifier: 'First'
                ),
                new BaselineWarning(
                    message: 'Second error',
                    count: 3,
                    path: 'src/Example.php',
                    identifier: 'Second'
                ),
                new BaselineWarning(
                    message: 'Third error',
                    count: 2,
                    path: 'src/Example.php',
                    identifier: 'Third'
                ),
                new BaselineWarning(
                    message: 'Fourth error',
                    count: 1,
                    path: 'src/Example.php',
                    identifier: 'Fourth'
                ),
            ]),
            new FileWarnings('src/ExampleTwo.php', [
                new BaselineWarning(
                    message: 'First and only error for ExampleTwo',
                    count: 2,
                    path: 'src/ExampleTwo.php',
                ),
            ]),
        ];

        // Act
        $this->formatter->output($warnings, 'github', $this->output);

        // Assert
        $expected =
            '::warning file=src/Example.php,line=0,title=PHPStan baselined errors::Found 4 errors that occur a total of 11 times:%0A- `First` : 5 times%0A- `Second` : 3 times%0A...2 more'."\n"
            .'::warning file=src/ExampleTwo.php,line=0,title=PHPStan baselined errors::Found 1 errors that occur a total of 2 times:%0A- `First and only error for ExampleTwo` : 2 times'."\n";

        $this->assertEquals($expected, $this->output->fetch());
    }
}
