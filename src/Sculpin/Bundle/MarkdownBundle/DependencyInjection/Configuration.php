<?php declare(strict_types=1);

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

        $rootNode = $treeBuilder->root('sculpin_markdown');

        $rootNode
            ->children()
                ->scalarNode('parser_class')
                    ->defaultValue(PhpMarkdownExtraParser::class)
                ->end()
                ->arrayNode('extensions')
                    ->defaultValue(['md', 'mdown', 'mkdn', 'markdown'])
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
