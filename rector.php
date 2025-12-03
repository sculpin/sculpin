<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Expression\TernaryFalseExpressionToIfRector;
use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\Symfony\Symfony61\Rector\Class_\CommandConfigureToAttributeRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withSkip([
        FirstClassCallableRector::class => [
            // The callable here is intended to be serialized, so must not be converted to Closure/FirstClassCallable
            __DIR__ . '/src/Sculpin/Bundle/ContentTypesBundle/DependencyInjection/SculpinContentTypesExtension.php',
        ],
        TernaryFalseExpressionToIfRector::class,
        CommandConfigureToAttributeRector::class,
    ])
    ->withPhpSets(php83: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        instanceOf: true,
        earlyReturn: true,
        rectorPreset: true,
        symfonyCodeQuality: true,
        symfonyConfigs: true,
    )
    ->withImportNames(importShortClasses: false, removeUnusedImports: true)
    ->withComposerBased(twig: true, phpunit: true, symfony: true)
    ;
