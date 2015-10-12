<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\SculpinBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Sculpin Extension.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class SculpinExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration;
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader(
            $container, new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.xml');

        foreach (
            array('source_dir', 'output_dir', 'exclude', 'ignore', 'raw',
                  'permalink') as $key
        ) {
            $this->setSculpinParameter($container, $config, $key);
        }
    }

    protected function setSculpinParameter(ContainerBuilder $container,
        array $config, $key
    ) {
        $container->setParameter('sculpin.' . $key, $config[$key]);
    }
}
