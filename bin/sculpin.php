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
