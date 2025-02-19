<?php

namespace Juampi92\PhpstanBaselineWarnings\Domain;

use Juampi92\PhpstanBaselineWarnings\Domain\ValueObject\BaselineWarning;
use Nette\Neon\Neon;

class Baseline
{
    /**
     * @param string $content NEON formatted content
     * @param string|null $baseDir Optional base directory for path normalization
     * @return array<BaselineWarning>
     */
    public function parse(
        string $content,
        ?string $baseDir = null,
    ): array
    {
        $neon = Neon::decode($content);

        return array_map(
            fn (array $error) => new BaselineWarning(
                message: $error['message'],
                count: $error['count'],
                path: $this->normalizePath($error['path'], $baseDir),
                identifier: $error['identifier'] ?? null
            ),
            $neon['parameters']['ignoreErrors'] ?? []
        );
    }

    private function normalizePath(string $path, ?string $baseDir): string
    {
        // If we have a baseDir and the path starts with it, trim it
        if ($baseDir !== null) {
            $path = $this->removeBaseDir($path, $baseDir);
        }

        // Remove any ./ from the beginning
        $path = preg_replace('/^\.\//', '', $path);

        // Resolve parent directory references (../)
        $parts = explode('/', $path);
        $stack = [];

        foreach ($parts as $part) {
            if ($part === '..') {
                array_pop($stack);
            } elseif ($part !== '.' && $part !== '') {
                $stack[] = $part;
            }
        }

        return implode('/', $stack);
    }

    private function removeBaseDir(string $path, string $baseDir): string
    {
        // Ensure baseDir has trailing slash for proper matching
        $baseDir = rtrim($baseDir, '/') . '/';

        // If path starts with baseDir, remove it
        if (str_starts_with($path, $baseDir)) {
            return substr($path, strlen($baseDir));
        }

        return $path;
    }
}
