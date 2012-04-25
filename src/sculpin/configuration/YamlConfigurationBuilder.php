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

use Dflydev\DotAccessConfiguration\YamlConfigurationBuilder as BaseYamlConfigurationBuilder;

class YamlConfigurationBuilder extends AbstractConfigurationBuilder
{
    /**
     * Constructor
     * 
     * @param array $yamlConfigurationFilenames
     */
    public function __construct($input = null)
    {
        $this->setConfigurationBuilder(new BaseYamlConfigurationBuilder($input));
    }
}