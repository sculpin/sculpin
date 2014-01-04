<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\PostsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
    * {@inheritdoc}
    */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder;

        $rootNode = $treeBuilder->root('sculpin_posts');

        $rootNode
            ->children()
                ->arrayNode('paths')
                    ->prototype('scalar')->end()
                ->end()
                ->booleanNode('publish_drafts')->end()
                ->scalarNode('permalink')->end()
                ->scalarNode('layout')->end()
            ->end();

        return $treeBuilder;
    }
}
