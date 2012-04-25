<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\configuration;

use Dflydev\DotAccessConfiguration\ConfigurationBuilderInterface;

abstract class AbstractConfigurationBuilder
{
    private $configurationBuilder;

    /**
     * Set Configuration Builder
     * 
     * @param ConfigurationBuilderInterface $configurationBuilder
     * @return AbstractConfigurationBuilder
     */
    protected function setConfigurationBuilder(ConfigurationBuilderInterface $configurationBuilder)
    {
        $this->configurationBuilder = $configurationBuilder;
        $this->configurationBuilder->setConfigurationFactory(new ConfigurationFactory);
    }
    
    /**
     * Build Configuration
     * 
     * @return Configuration
     */
    public function build()
    {
        return $this->configurationBuilder->build();
    }
}