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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
final class DataSourcePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('sculpin.data_source')) {
            return;
        }

        $definition = $container->getDefinition('sculpin.data_source');

        foreach ($container->findTaggedServiceIds('sculpin.data_source') as $id => $tagAttributes) {
            $definition->addMethodCall('addDataSource', [new Reference($id)]);
        }
    }
}
