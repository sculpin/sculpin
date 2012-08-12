<?php

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
class Factory
{
    /**
     * Default root dir
     *
     * @var string
     */
    const DEFAULT_ROOT_DIR = '.';

    /**
     * Create a kernel.
     *
     * @param InputInterface $input Input
     *
     * @return Symfony\Component\HttpKernel\Kernel
     */
    public static function create(InputInterface $input)
    {
        $env = $input->getParameterOption(array('--env', '-e'), getenv('SCULPIN_DEBUG') ?: 'dev');
        $debug = getenv('SCULPIN_DEBUG') !== '0' && !$input->hasParameterOption(array('--no-debug', ''));

        // do something here to locate and try to create
        // a custom kernel.

        $rootDir = realpath($input->getParameterOption('--root-dir') ?: self::DEFAULT_ROOT_DIR);

        if (file_exists($customKernel = $rootDir.'/config/SculpinKernel.php')) {
            require $customKernel;

            return new \SculpinKernel($env, $debug, $rootDir);
        }

        // Fallback to using the default kernel in case
        // user does not define their own kernel somehow.
        return new DefaultKernel($env, $debug, $rootDir);
    }
}
