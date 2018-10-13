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
 * Adds tagged twig.extension services to twig service
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Beau Simensen <beau@dflydev.com>
 */
class TwigEnvironmentPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasDefinition('sculpin_twig.twig')) {
            return;
        }

        $definition = $container->getDefinition('sculpin_twig.twig');

        // Extensions must always be registered before everything else.
        // For instance, global variable definitions must be registered
        // afterward. If not, the globals from the extensions will never
        // be registered.
        $calls = $definition->getMethodCalls();
        $definition->setMethodCalls([]);
        foreach ($container->findTaggedServiceIds('twig.extension') as $id => $attributes) {
            $definition->addMethodCall('addExtension', [new Reference($id)]);
        }
        $definition->setMethodCalls(array_merge($definition->getMethodCalls(), $calls));
    }
}
