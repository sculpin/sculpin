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

use dflydev\util\antPathMatcher\AntPathMatcher;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Path Configurator pass
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class PathConfiguratorPass implements CompilerPassInterface
{
    /**
     * Matcher
     *
     * @var AntPathMatcher
     */
    protected $matcher;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->matcher = new AntPathMatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('sculpin.path_configurator') as $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $typeParameter = 'sculpin.'.$attributes['type'];
                $parameter = $attributes['parameter'];

                if ($container->hasParameter($parameter)) {
                    $value = $container->getParameter($parameter);
                    if (! is_array($value)) {
                        $value = [$value];
                    }

                    if ($container->hasParameter($typeParameter)) {
                        $container->setParameter($typeParameter, array_unique(array_merge(
                            $container->getParameter($typeParameter),
                            $this->antify($value)
                        )));
                    } else {
                        $container->setParameter($typeParameter, array_unique($this->antify($value)));
                    }
                }
            }
        }
    }

    protected function antify($paths)
    {
        $matcher = $this->matcher;

        return array_map(
            function ($path) use ($matcher) {
                if ($matcher->isPattern($path)) {
                    return $path;
                }

                return $path.'/**';
            },
            $paths
        );
    }
}
