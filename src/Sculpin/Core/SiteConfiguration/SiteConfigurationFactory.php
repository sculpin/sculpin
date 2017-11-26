<?php declare(strict_types=1);

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
    public function __construct(string $rootDir, string $environment)
    {
        $this->rootDir = $rootDir;
        $this->environment = $environment;
    }

    /**
     * Get an instance of the Configuration() class from the given file.
     *
     * @param  string $configFile
     * @return YamlFileConfigurationBuilder
     */
    private function getConfigFile(string $configFile): YamlFileConfigurationBuilder
    {
        $builder = new YamlFileConfigurationBuilder([$configFile]);

        return $builder->build();
    }

    /**
     * Create Site Configuration
     *
     * @return Configuration
     */
    public function create(): Configuration
    {
        $config = $this->detectConfig();
        $config->set('env', $this->environment);

        return $config;
    }
    /**
     * Detect configuration file and create Site Configuration from it
     *
     * @return Configuration
     */
    public function detectConfig(): Configuration
    {
        if (file_exists($file = $this->rootDir.'/config/sculpin_site_'.$this->environment.'.yml')) {
            return $this->getConfigFile($file);
        }

        if (file_exists($file = $this->rootDir.'/config/sculpin_site.yml')) {
            return $this->getConfigFile($file);
        }

        return  new Configuration();
    }
}
