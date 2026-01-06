<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Symfony\CodeQuality\Rector\Class_\ControllerMethodInjectionToConstructorRector;
use Rector\Symfony\Set\SymfonySetList;

return RectorConfig::configure()
    ->withRootFiles()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        __DIR__ . '/var',
        __DIR__ . '/vendor',
        __DIR__ . '/migrations',
        __DIR__ . '/src/Kernel.php',
        __DIR__ . '/config/bundles.php',
        __DIR__ . '/config/reference.php',
        ControllerMethodInjectionToConstructorRector::class,
    ])
    ->withPhpSets(
        php85: true
    )
    ->withAttributesSets(
        symfony: true,
        doctrine: true,
        phpunit: true,
    )
    ->withComposerBased(
        twig: true,
        doctrine: true,
        phpunit: true,
        symfony: true,
    )
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: false,
        naming: false,
        instanceOf: true,
        earlyReturn: true,
        strictBooleans: false,
        carbon: false,
        rectorPreset: true,
        phpunitCodeQuality: true,
        doctrineCodeQuality: true,
        symfonyCodeQuality: true,
        symfonyConfigs: true,
    )
    ->withImportNames(
        importShortClasses: false,
        removeUnusedImports: true,
    )
    ->withParallel(
        timeoutSeconds: 300,
        maxNumberOfProcess: 4,
        jobSize: 20
    )
    ->withCache(__DIR__ . '/var/cache/rector')
;
