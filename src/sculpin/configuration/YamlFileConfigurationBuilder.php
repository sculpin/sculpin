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

use Dflydev\DotAccessConfiguration\YamlFileConfigurationBuilder as BaseYamlFileConfigurationBuilder;

class YamlFileConfigurationBuilder extends AbstractConfigurationBuilder
{
    /**
     * Constructor
     * 
     * @param array $yamlConfigurationFilenames
     */
    public function __construct(array $yamlConfigurationFilenames)
    {
        $this->setConfigurationBuilder(new BaseYamlFileConfigurationBuilder($yamlConfigurationFilenames));
    }
}