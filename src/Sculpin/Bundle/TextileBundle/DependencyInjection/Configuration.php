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

namespace Sculpin\Bundle\TextileBundle\DependencyInjection;

use Netcarver\Textile\Parser;
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
        $treeBuilder = new TreeBuilder('sculpin_textile');

        $rootNode = $treeBuilder->getRootNode();

        // @phpstan-ignore method.notFound
        $rootNode
            ->children()
                ->scalarNode('parser_class')->defaultValue(Parser::class)->end()
                ->arrayNode('extensions')
                    ->defaultValue(['textile'])
                    ->scalarPrototype()->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
