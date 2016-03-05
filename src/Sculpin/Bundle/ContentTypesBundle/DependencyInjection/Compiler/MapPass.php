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
        $typesId = self::generateId('types');
        $types = $container->getParameter($typesId);

        foreach ($types as $type) {
            $mapId = self::generateTypesId($type, 'map');

            if (false === $container->hasDefinition($mapId)) {
                continue;
            }

            $definition = $container->getDefinition($mapId);

            foreach ($container->findTaggedServiceIds($mapId) as $id => $tagAttributes) {
                foreach ($tagAttributes as $attributes) {
                    $definition->addMethodCall('addMap', array(new Reference($id)));
                }
            }
        }
    }

    private static function generateId($value)
    {
        return implode('.', array('sculpin_content_types', $value));
    }

    private static function generateTypesId($type, $value)
    {
        return implode('.', array('sculpin_content_types.types', $type, $value));
    }
}
