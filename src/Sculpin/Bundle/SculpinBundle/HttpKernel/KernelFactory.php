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

/**
 * Kernel Factory
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class KernelFactory
{
    /**
     * Create a kernel.
     *
     * @param InputInterface $input Input
     *
     * @return \Symfony\Component\HttpKernel\Kernel
     */
    public static function create(InputInterface $input)
    {
        $env = $input->getParameterOption(['--env', '-e'], getenv('SCULPIN_DEBUG') ?: 'dev');
        $debug = (
            $env !== 'prod'
            && getenv('SCULPIN_DEBUG') !== '0'
            && !$input->hasParameterOption(['--no-debug', ''])
        );

        $outputDir = $input->getParameterOption(['--output-dir'], getenv('SCULPIN_OUTPUT_DIR') ?: null);

        // do something here to locate and try to create
        // a custom kernel.

        // We are relying on our calling script to chdir as appropriate with any
        // --project-directory that was specified.
        $projectDir = getcwd();

        $overrides = [
            'projectDir' => $projectDir,
            'outputDir'  => $outputDir,
        ];

        if (file_exists($customKernel = $projectDir.'/app/SculpinKernel.php')) {
            require $customKernel;

            return new \SculpinKernel($env, $debug, $overrides);
        }

        // Fallback to using the default kernel in case
        // user does not define their own kernel somehow.
        return new DefaultKernel($env, $debug, $overrides);
    }
}
