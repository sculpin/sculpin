<?php declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\StandaloneBundle\DependencyInjection\Compiler;

use InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Register Kernel Listener Pass
 *
 * Originally from FrameworkBundle/DependencyInjection/Compiler/RegisterKernelListenersPass.php
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class RegisterKernelListenersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('event_dispatcher')) {
            return;
        }

        $definition = $container->getDefinition('event_dispatcher');

        foreach ($container->findTaggedServiceIds('kernel.event_listener') as $id => $events) {
            foreach ($events as $event) {
                $priority = $event['priority'] ?? 0;

                if (!isset($event['event'])) {
                    throw new InvalidArgumentException(sprintf(
                        'Service "%s" must define the "event" attribute on "kernel.event_listener" tags.',
                        $id
                    ));
                }

                if (!isset($event['method'])) {
                    $event['method'] = 'on'.preg_replace_callback([
                        '/(?<=\b)[a-z]/i',
                        '/[^a-z0-9]/i',
                    ], function ($matches) {
                        return strtoupper($matches[0]);
                    }, $event['event']);
                    $event['method'] = preg_replace('/[^a-z0-9]/i', '', $event['method']);
                }

                $definition->addMethodCall(
                    'addListenerService',
                    [$event['event'], [$id, $event['method']], $priority]
                );
            }
        }

        foreach ($container->findTaggedServiceIds('kernel.event_subscriber') as $id => $attributes) {
            // We must assume that the class value has been correctly filled,
            // even if the service is created by a factory
            $class = $container->getDefinition($id)->getClass();

            $refClass = new ReflectionClass($class);
            $interface = EventSubscriberInterface::class;
            if (!$refClass->implementsInterface($interface)) {
                throw new InvalidArgumentException(sprintf(
                    'Service "%s" must implement interface "%s".',
                    $id,
                    $interface
                ));
            }

            $definition->addMethodCall('addSubscriberService', [$id, $class]);
        }
    }
}
