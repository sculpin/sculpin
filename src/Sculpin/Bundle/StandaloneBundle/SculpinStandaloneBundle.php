<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\StandaloneBundle;

use Sculpin\Bundle\StandaloneBundle\DependencyInjection\Compiler\RegisterKernelListenersPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Standalone Bundle.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class SculpinStandaloneBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterKernelListenersPass, PassConfig::TYPE_AFTER_REMOVING);
    }
}
