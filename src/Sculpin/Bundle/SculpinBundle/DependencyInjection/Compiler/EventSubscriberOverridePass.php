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
 * Make sure that all services tagged with kernel.event_subscriber are public.
 */
final class EventSubscriberOverridePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $services = $container->findTaggedServiceIds('kernel.event_subscriber');
        foreach ($services as $id => $tags) {
            $definition = $container->getDefinition($id);
            $definition->setPublic(true);
        }
    }
}
