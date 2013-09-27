<?php

namespace Sculpin\Bundle\PostsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class PostsMapPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('sculpin_posts.posts_map')) {
            return;
        }

        $definition = $container->getDefinition('sculpin_posts.posts_map');

        foreach ($container->findTaggedServiceIds('sculpin_posts.posts_map') as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $definition->addMethodCall('addMap', array(new Reference($id)));
            }
        }
    }
}
