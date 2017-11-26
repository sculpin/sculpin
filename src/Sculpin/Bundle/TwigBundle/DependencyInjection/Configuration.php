<?php declare(strict_types=1);

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

        $rootNode = $treeBuilder->root('sculpin_twig');

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
            ->end();

        return $treeBuilder;
    }
}
