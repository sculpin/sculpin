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

namespace Sculpin\Bundle\PostsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
class SculpinPostsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration;
        $config = $this->processConfiguration($configuration, $configs);

        if (count($config['paths']) > 0 || count($config) > 1) {
            throw new InvalidConfigurationException(
                // @codingStandardsIgnoreLine
                "Posts are now configured in the 'sculpin_content_types' section of sculpin_kernel.yml, please see documentation on configuring content types"
            );
        }
    }
}
