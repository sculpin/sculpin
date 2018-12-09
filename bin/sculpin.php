<?php

declare(strict_types=1);

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
    @ini_set('display_errors', '1');

    $memoryInBytes = function ($value): int {
        $unit = strtolower(substr($value, -1, 1));
        $value = (int) $value;
        switch ($unit) {
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

use Sculpin\Bundle\SculpinBundle\Console\Application;
use Sculpin\Bundle\SculpinBundle\HttpKernel\KernelFactory;
use Symfony\Component\Console\Input\ArgvInput;

$input = new ArgvInput;

if ($projectDir = $input->getParameterOption('--project-dir')) {
    if (false !== strpos($projectDir, '~') && function_exists('posix_getuid')) {
        $info = posix_getpwuid(posix_getuid());
        $projectDir = str_replace('~', $info['dir'], $projectDir);
    }

    if (! is_dir($projectDir)) {
        throw new \InvalidArgumentException(
            sprintf('Specified project directory %s does not exist', $projectDir)
        );
    }

    chdir($projectDir);
}

$kernel = KernelFactory::create($input);
$application = new Application($kernel);
$application->run($input);
