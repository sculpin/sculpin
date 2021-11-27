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

namespace Sculpin\Bundle\SculpinBundle\HttpKernel;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
final class KernelFactory
{
    public static function create(InputInterface $input): Kernel
    {
        $env = $input->getParameterOption(['--env', '-e'], getenv('SCULPIN_DEBUG') ?: 'dev');
        $debug = (
            $env !== 'prod'
            && getenv('SCULPIN_DEBUG') !== '0'
            && !$input->hasParameterOption(['--no-debug', ''])
        );

        $outputDir = $input->getParameterOption(['--output-dir'], getenv('SCULPIN_OUTPUT_DIR') ?: null);
        $sourceDir = $input->getParameterOption(['--source-dir'], getenv('SCULPIN_SOURCE_DIR') ?: null);

        // do something here to locate and try to create
        // a custom kernel.

        // We are relying on our calling script to chdir as appropriate with any
        // --project-directory that was specified.
        $projectDir = getcwd();

        $overrides = [
            'projectDir' => $projectDir,
            'outputDir'  => $outputDir,
            'sourceDir'  => $sourceDir,
        ];

        if (file_exists($customKernel = $projectDir.'/app/SculpinKernel.php')) {
            require $customKernel;
            $customKernelClass = '\SculpinKernel';

            if (!class_exists($customKernelClass)) {
                throw new \RuntimeException("Unable to find custom kernel class in file $customKernel");
            }

            return new $customKernelClass($env, $debug, $overrides);
        }

        // Fallback to using the default kernel in case
        // user does not define their own kernel somehow.
        return new DefaultKernel($env, $debug, $overrides);
    }
}
