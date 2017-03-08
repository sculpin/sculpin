<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\ContentTypesBundle\DependencyInjection;

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
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('sculpin_content_types');

        $contentTypeNode = $rootNode
            ->useAttributeAsKey('name')
            ->prototype('array')
        ;

        $contentTypeNode
            ->children()
                ->scalarNode('type')->defaultValue('path')->end()
                ->scalarNode('singular_name')->end()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->arrayNode('path')
                    ->beforeNormalization()
                        // Default case is we want the user to specify just one
                        // path but we can allow for multiple if they want to.
                        ->ifString()
                        ->then(function ($v) {
                            return array($v);
                        })
                    ->end()
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('meta_key')->end()
                ->scalarNode('meta')->end()
                ->booleanNode('publish_drafts')->defaultNull()->end()
                ->scalarNode('permalink')->end()
                ->scalarNode('layout')->end()
                ->arrayNode('taxonomies')
                    ->beforeNormalization()
                        // Default case is we want the user to specify just one
                        // taxonomy but we can allow for multiple if they want to.
                        ->ifString()
                        ->then(function ($v) {
                            return array(array('name' => $v));
                        })
                    ->end()
                    ->prototype('array')
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($v) {
                                return array('name' => $v);
                            })
                        ->end()
                        ->children()
                            ->scalarNode('name')->end()
                            ->arrayNode('strategies')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
