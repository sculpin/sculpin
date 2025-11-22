<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withSkip([
        \Rector\Php81\Rector\Array_\FirstClassCallableRector::class => [
            // The callable here is intended to be serialized, so must not be converted to Closure/FirstClassCallable
            __DIR__ . '/src/Sculpin/Bundle/ContentTypesBundle/DependencyInjection/SculpinContentTypesExtension.php',
        ],
    ])
    ->withPhpSets(php83: true)
    ->withPhpVersion(PhpVersion::PHP_83)
    ->withPreparedSets(deadCode: true, codeQuality: true, symfonyCodeQuality: true)
    ->withImportNames(importShortClasses: false, removeUnusedImports: true)
    ->withTypeCoverageLevel(0);
