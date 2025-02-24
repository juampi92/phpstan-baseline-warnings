<?php

namespace Tests\Unit;

use Juampi92\PhpstanBaselineWarnings\Domain\Baseline;
use PHPUnit\Framework\TestCase;

class BaselineTest extends TestCase
{
    public function test_it_can_parse_baseline_with_standard_paths(): void
    {
        // Arrange
        $content = file_get_contents(__DIR__.'/fixtures/baseline-with-standard-paths.neon');

        // Act
        $baseline = new Baseline;
        $warnings = $baseline->parse($content, baseDir: './');

        // Assert
        $this->assertCount(2, $warnings);
        $this->assertEquals('src/Application/Baseline/BaselineCollection.php', $warnings[0]->path);
        $this->assertEquals(2, $warnings[0]->count);
        $this->assertEquals('src/Application/Formatters/ConsoleFormatter.php', $warnings[1]->path);
        $this->assertEquals(1, $warnings[1]->count);
    }

    public function test_it_can_parse_baseline_with_relative_paths(): void
    {
        // Arrange
        $content = file_get_contents(__DIR__.'/fixtures/baseline-with-relative-paths.neon');

        // Act
        $baseline = new Baseline;
        $warnings = $baseline->parse($content, baseDir: '../../');

        // Assert
        $this->assertCount(2, $warnings);
        $this->assertEquals('src/Application/Baseline/BaselineCollection.php', $warnings[0]->path);
        $this->assertEquals(1, $warnings[0]->count);
        $this->assertNull($warnings[0]->identifier);

        $this->assertEquals('src/Application/Formatters/ConsoleFormatter.php', $warnings[1]->path);
        $this->assertEquals(2, $warnings[1]->count);
        $this->assertNull($warnings[1]->identifier);
    }

    public function test_it_works_with_idenfifiers(): void
    {
        // Arrange
        $content = file_get_contents(__DIR__.'/fixtures/baseline-with-identifiers.neon');

        // Act
        $baseline = new Baseline;
        $warnings = $baseline->parse($content);

        // Assert
        $this->assertCount(2, $warnings);
        $this->assertEquals('src/BaselineWarningsCommand.php', $warnings[0]->path);
        $this->assertEquals(1, $warnings[0]->count);
        $this->assertEquals('method.notFound', $warnings[0]->identifier);
    }
}
