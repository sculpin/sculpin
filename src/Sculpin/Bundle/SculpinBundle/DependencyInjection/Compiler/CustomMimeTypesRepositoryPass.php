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

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
final class CustomMimeTypesRepositoryPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('sculpin.custom_mime_types_repository')) {
            return;
        }

        $definition = $container->getDefinition('sculpin.custom_mime_types_repository');

        $data = [];
        foreach ($container->findTaggedServiceIds('sculpin.custom_mime_extensions') as $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $type = $attributes['type'];
                $parameter = $attributes['parameter'];

                if ($container->hasParameter($parameter)) {
                    if (isset($data[$type])) {
                        $data[$type] = array_unique(array_merge(
                            $container->getParameter($type),
                            $container->getParameter($parameter)
                        ));
                    } else {
                        $data[$type] = array_unique($container->getParameter($parameter));
                    }
                }
            }
        }

        foreach ($data as $type => $extensions) {
            $data[$type] = array_filter($extensions, function ($var) {
                return strlen($var) > 0;
            });
        }

        $definition->addArgument($data);
    }
}
