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

namespace Sculpin\Bundle\SculpinBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
final class WriterPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('sculpin.writer')) {
            return;
        }

        if (false === $container->hasParameter('sculpin.output_dir_override')) {
            return;
        }

        $override   = $container->getParameter('sculpin.output_dir_override');
        $definition = $container->getDefinition('sculpin.writer');

        if (!$override) {
            return;
        }

        $definition->addMethodCall('setOutputDir', [$override]);
    }
}
