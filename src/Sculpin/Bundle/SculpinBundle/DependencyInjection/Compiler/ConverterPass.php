<?php

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
use Symfony\Component\DependencyInjection\Reference;

/**
 * Converter pass
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class ConverterPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('sculpin')) {
            return;
        }

        $definition = $container->getDefinition('sculpin');

        foreach ($container->findTaggedServiceIds('sculpin.converter') as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $definition->addMethodCall('registerConverter', array($id, new Reference($id)));
                $definition->addMethodCall('registerConverter', array($attributes['alias'], new Reference($id)));
            }
        }
    }
}
