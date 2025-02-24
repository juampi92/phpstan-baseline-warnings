<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Juampi92\PhpstanBaselineWarnings\Application\Command\BaselineWarningsCommand;
use Juampi92\PhpstanBaselineWarnings\Application\WarningsOutputFormatter;
use Juampi92\PhpstanBaselineWarnings\Domain\BaselineParser;
use Juampi92\PhpstanBaselineWarnings\Domain\BaselineWarningsCorrelator;
use Juampi92\PhpstanBaselineWarnings\Domain\Storage\BaselineStorage;
use Juampi92\PhpstanBaselineWarnings\Infrastructure\Storage\FileBaselineStorage;

$containerBuilder = new ContainerBuilder;

$containerBuilder->addDefinitions([
    BaselineStorage::class => \DI\create(FileBaselineStorage::class),
    BaselineParser::class => \DI\create()
        ->constructor(\DI\get(BaselineStorage::class)),
    WarningsOutputFormatter::class => \DI\create()
        ->constructor(),
    BaselineWarningsCorrelator::class => \DI\create(),
    BaselineWarningsCommand::class => \DI\create()
        ->constructor(
            \DI\get(BaselineParser::class),
            \DI\get(BaselineWarningsCorrelator::class),
            \DI\get(WarningsOutputFormatter::class)
        ),
]);

return $containerBuilder->build();
