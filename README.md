# PHPStan Baseline Warnings

<p align="center">
    <a href="https://packagist.org/packages/juampi92/phpstan-baseline-warnings"><img src="https://img.shields.io/packagist/v/juampi92/phpstan-baseline-warnings.svg?style=flat-square" alt="Latest Version on Packagist"></a>
    <a href="https://packagist.org/packages/juampi92/phpstan-baseline-warnings"><img src="https://img.shields.io/packagist/dm/juampi92/phpstan-baseline-warnings.svg?style=flat-square" alt="Downloads Per Month"></a>
    <a href="https://github.com/juampi92/phpstan-baseline-warnings/actions?query=workflow%3Atests+branch%3Amain"><img src="https://img.shields.io/github/workflow/status/juampi92/phpstan-baseline-warnings/tests?label=tests&style=flat-square" alt="GitHub Tests Action Status"></a>
    <a href="https://packagist.org/packages/juampi92/phpstan-baseline-warnings"><img src="https://img.shields.io/packagist/php-v/juampi92/phpstan-baseline-warnings.svg?style=flat-square" alt="PHP from Packagist"></a>
</p>

A Composer package that analyzes PHPStan baseline files and generates GitHub-compatible warning annotations for files with baseline-ignored errors. This helps teams maintain code quality by highlighting technical debt that's been temporarily suppressed through baselines.

## Features

- Analyzes PHPStan baseline files to identify suppressed errors
- Generates GitHub-compatible warning annotations
- Helps track and manage technical debt in your codebase

## Requirements

- PHP 8.1 or higher
- Composer

## Installation

You can install the package via composer:

```bash
composer require --dev juampi92/phpstan-baseline-warnings
```

## Usage

After installing, you can run the command to analyze specific files against your PHPStan baseline:

```bash
# Basic usage with default baseline path
vendor/bin/phpstan-baseline-warnings src/MyClass.php src/OtherClass.php

# Specify a custom baseline path and base directory
vendor/bin/phpstan-baseline-warnings \
    --baseline-path=tools/phpstan-baseline.neon \
    --base-dir=../ \
    src/Domain/Entity/Post.php \
    src/Domain/Repository/PostRepository.php
```

The command will:
1. Read your PHPStan baseline file (default: `phpstan-baseline.neon`)
2. Check if the provided files have any baseline-ignored errors
3. Generate GitHub-compatible warning annotations for those files

### Options

- `--baseline-path`: Path to your PHPStan baseline file (default: `./phpstan-baseline.neon`)
- `--base-dir`: Base directory for resolving relative paths. Useful when your phpstan config is inside a sub-folder (default: `./`)

## GitHub Action

You can also use this tool as a GitHub Action in your workflows:

```yaml
name: PHPStan Baseline Warnings
on:
  pull_request:

permissions:
  pull-requests: read
  contents: read

jobs:
  phpstan-baseline-warnings:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with:
          fetch-depth: 0  # Required to fetch git history
          
      - name: PHPStan Baseline Warnings
        uses: juampi92/phpstan-baseline-warnings@v1
        with:
          baseline-path: tools/phpstan-baseline.neon
          base-dir: ../
```

This will analyze your PHPStan baseline file and create warning annotations in your GitHub repository.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the MIT license.
