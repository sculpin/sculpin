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

namespace Sculpin\Bundle\TwigBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
    * {@inheritdoc}
    */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sculpin_twig');

        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('view_paths')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('source_view_paths')
                    ->defaultValue(['_views', '_layouts', '_includes', '_partials'])
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('extensions')
                    ->defaultValue(['', 'twig', 'html', 'html.twig', 'twig.html'])
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('webpack_manifest')
                    ->info(
                        'Optional relative path to the Webpack Encore manifest file within source/ '
                        . '(e.g., "build/manifest.json")'
                    )
                    ->defaultNull()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
