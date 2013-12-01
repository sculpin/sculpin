<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

set_time_limit(0);

if (function_exists('ini_set')) {
    @ini_set('display_errors', 1);

    $memoryInBytes = function ($value) {
        $unit = strtolower(substr($value, -1, 1));
        $value = (int) $value;
        switch($unit) {
            case 'g':
                $value *= 1024;
                // no break (cumulative multiplier)
            case 'm':
                $value *= 1024;
                // no break (cumulative multiplier)
            case 'k':
                $value *= 1024;
        }

        return $value;
    };

    $memoryLimit = trim(ini_get('memory_limit'));
    // Increase memory_limit if it is lower than 512M
    if ($memoryLimit != -1 && $memoryInBytes($memoryLimit) < 512 * 1024 * 1024) {
        @ini_set('memory_limit', '512M');
    }
    unset($memoryInBytes, $memoryLimit);
}

use Dflydev\EmbeddedComposer\Core\EmbeddedComposerBuilder;
use Sculpin\Bundle\SculpinBundle\Console\Application;
use Sculpin\Bundle\SculpinBundle\HttpKernel\KernelFactory;
use Symfony\Component\Console\Input\ArgvInput;

$input = new ArgvInput;

$projectDir = $input->getParameterOption('--project-dir') ?: null;

$embeddedComposerBuilder = new EmbeddedComposerBuilder(
    $classLoader,
    $projectDir
);

$embeddedComposer = $embeddedComposerBuilder
    ->setComposerFilename('sculpin.json')
    ->setVendorDirectory('.sculpin')
    ->build();

$embeddedComposer->processAdditionalAutoloads();

$kernel = KernelFactory::create($input);
$application = new Application($kernel, $embeddedComposer);
$application->run($input);
