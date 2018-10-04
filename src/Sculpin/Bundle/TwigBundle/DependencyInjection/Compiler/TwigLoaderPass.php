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

namespace Sculpin\Bundle\TwigBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds tagged twig.loaders services to twig loader service
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Beau Simensen <beau@dflydev.com>
 */
class TwigLoaderPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasDefinition('sculpin_twig.loader')) {
            return;
        }

        $definition = $container->getDefinition('sculpin_twig.loader');
        $arguments = $definition->getArguments();
        $loaders = $arguments[0];

        $prependedLoaders = [];
        $appendedLoaders = [];

        foreach ($container->findTaggedServiceIds('twig.loaders.prepend') as $id => $attributes) {
            $prependedLoaders[] = new Reference($id);
        }

        foreach ($container->findTaggedServiceIds('twig.loaders.append') as $id => $attributes) {
            $appendedLoaders[] = new Reference($id);
        }

        $sourceViewPaths = $container->getParameter('sculpin_twig.source_view_paths');
        foreach ($container->getParameter('kernel.bundles') as $class) {
            $reflection = new \ReflectionClass($class);
            foreach ($sourceViewPaths as $sourceViewPath) {
                if (is_dir($dir = dirname($reflection->getFileName()).'/Resources/'.$sourceViewPath)) {
                    $appendedLoaders[] = $dir;
                }
            }
        }

        $arguments[0] = array_merge($prependedLoaders, $loaders, $appendedLoaders);
        $definition->setArguments($arguments);
    }
}
