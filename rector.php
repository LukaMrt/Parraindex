<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::PHP_83,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
        SetList::PRIVATIZATION,
        SetList::NAMING,
        SetList::STRICT_BOOLEANS,
        SetList::RECTOR_PRESET,
        SetList::INSTANCEOF,

        SymfonySetList::SYMFONY_71,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::CONFIGS,

        PHPUnitSetList::PHPUNIT_110,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,

        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        DoctrineSetList::DOCTRINE_CODE_QUALITY,
        DoctrineSetList::TYPED_COLLECTIONS,
        DoctrineSetList::YAML_TO_ANNOTATIONS,
        DoctrineSetList::DOCTRINE_ORM_213,
        DoctrineSetList::DOCTRINE_DBAL_40,
        DoctrineSetList::DOCTRINE_BUNDLE_210,
    ]);

    // Optional: Exclude specific files or directories
    $rectorConfig->skip([
        __DIR__ . '/var',
        __DIR__ . '/vendor',
    ]);

    $rectorConfig->cacheDirectory(__DIR__ . '/var/cache/rector');
};
