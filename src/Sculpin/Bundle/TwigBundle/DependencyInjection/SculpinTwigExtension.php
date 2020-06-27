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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
class SculpinTwigExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration;
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('sculpin_twig.source_view_paths', $config['source_view_paths']);
        $container->setParameter('sculpin_twig.view_paths', $config['view_paths']);
        $container->setParameter('sculpin_twig.extensions', $config['extensions']);
        $container->setParameter('sculpin_twig.webpack_manifest', $config['webpack_manifest']);

        if (! extension_loaded('intl')) {
            // Do not enable the intl Twig extension if the intl PHP extension is not installed.
            $container->removeDefinition('sculpin_twig.extensions.intl');
        }
    }
}
