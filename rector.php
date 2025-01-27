<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkipPath(__DIR__ . '/src/ArrayObjectTrait.php')
    ->withSkipPath(__DIR__ . '/tests/_support/_generated/*')
    ->withRootFiles()
    ->withPhpSets()
//  ->withImportNames(
//      true,
//      true,
//      false,
//      true
//  )
    ->withSets([
        \Rector\Set\ValueObject\SetList::DEAD_CODE,
        \Rector\Set\ValueObject\SetList::CODE_QUALITY,
        \Rector\Set\ValueObject\SetList::CODING_STYLE,
        \Rector\Set\ValueObject\SetList::EARLY_RETURN,
        \Rector\Set\ValueObject\SetList::TYPE_DECLARATION,
        \Rector\Set\ValueObject\SetList::PRIVATIZATION,
        \Rector\Set\ValueObject\SetList::RECTOR_PRESET,
        \Rector\Set\ValueObject\SetList::INSTANCEOF,
    ]);
