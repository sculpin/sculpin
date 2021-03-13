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

namespace Sculpin\Bundle\MarkdownBundle\DependencyInjection;

use Sculpin\Bundle\MarkdownBundle\PhpMarkdownExtraParser;
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
        $treeBuilder = new TreeBuilder('sculpin_markdown');

        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('parser_class')
                    ->info('The markdown parser to use - must be a class without any constructor arguments')
                    ->defaultValue(PhpMarkdownExtraParser::class)
                ->end()
                ->arrayNode('extensions')
                    ->info('File name extensions to handle as markdown')
                    ->defaultValue(['md', 'mdown', 'mkdn', 'markdown'])
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
