<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\SiteConfiguration;

use Dflydev\DotAccessConfiguration\Configuration;
use Dflydev\DotAccessConfiguration\YamlFileConfigurationBuilder;

/**
 * Site Configuration Factory.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class SiteConfigurationFactory
{
    /**
     * Constructor.
     *
     * @param string $rootDir     Root directory
     * @param string $environment Environment
     */
    public function __construct($rootDir, $environment)
    {
        $this->rootDir = $rootDir;
        $this->environment = $environment;
    }

    /**
     * Create Site Configuration
     *
     * @return Configuration
     */
    public function create()
    {
        if (file_exists($file = $this->rootDir.'/config/sculpin_site_'.$this->environment.'.yml')) {
            $builder = new YamlFileConfigurationBuilder(array($file));

            return $builder->build();
        } elseif(file_exists($file = $this->rootDir.'/config/sculpin_site.yml')) {
            $builder = new YamlFileConfigurationBuilder(array($file));

            return $builder->build();
        }

        return new Configuration;
    }
}
