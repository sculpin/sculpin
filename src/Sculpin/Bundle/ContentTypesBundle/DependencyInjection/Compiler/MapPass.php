<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\ContentTypesBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class MapPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $types = $container->getParameter('sculpin_content_types.types');

        foreach ($container->findTaggedServiceIds('sculpin.content_type.map') as $id => $tagAttributes) {
            if (!isset($tagAttributes[0]['type'])) {
                throw new \InvalidArgumentException(sprintf('Service "%s" must define the "type" attribute on "%s" tags.',
                  $id, 'sculpin.content_type.map'));
            }

            if (!in_array($tagAttributes[0]['type'], $types)) {
                throw new \InvalidArgumentException(sprintf('Service "%s" type attribute on "%s" tags is not an active content type.',
                  $id, 'sculpin.content_type.map'));
            }

            $type = $tagAttributes[0]['type'];
            $mapId = 'sculpin_content_types.map.chain.'. $type;

            if (false === $container->hasDefinition($mapId)) {
                continue;
            }

            $definition = $container->getDefinition($mapId);
            $definition->addMethodCall('addMap', array(new Reference($id)));
        }
    }
}
