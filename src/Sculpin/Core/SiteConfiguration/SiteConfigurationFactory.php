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

namespace Sculpin\Core\SiteConfiguration;

use Dflydev\DotAccessConfiguration\Configuration;
use Dflydev\DotAccessConfiguration\ConfigurationInterface;
use Dflydev\DotAccessConfiguration\YamlFileConfigurationBuilder;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
final readonly class SiteConfigurationFactory
{
    public function __construct(private string $projectDir, private string $environment)
    {
    }

    /**
     * Get an instance of the Configuration() class from the given file.
     *
     *
     */
    private function getConfigFile(string $configFile): ConfigurationInterface
    {
        $builder = new YamlFileConfigurationBuilder([$configFile]);

        return $builder->build();
    }

    /**
     * Create Site Configuration
     */
    public function create(): ConfigurationInterface
    {
        $config = $this->detectConfig();
        $config->set('env', $this->environment);

        return $config;
    }

    /**
     * Detect configuration file and create Site Configuration from it
     *
     * @return ConfigurationInterface
     */
    public function detectConfig(): ConfigurationInterface
    {
        if (file_exists($file = $this->projectDir . '/app/config/sculpin_site_' . $this->environment . '.yml')) {
            return $this->getConfigFile($file);
        }

        if (file_exists($file = $this->projectDir . '/app/config/sculpin_site.yml')) {
            return $this->getConfigFile($file);
        }

        return new Configuration();
    }
}
