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

use Symfony\Component\Yaml\Yaml;

class YamlConfigurationBuilder implements IConfigurationBuilder {
    
    /**
     * YAML input string
     * @var string
     */
    private $input;

    /**
     * Constructor
     * @param string $input
     */
    public function __construct($input = null)
    {
        $this->input = $input;
    }
    
    /**
     * (non-PHPdoc)
     * @see sculpin\configuration.IConfigurationBuilder::build()
     */
    public function build()
    {
        if ($this->input) {
            return new Configuration(Yaml::parse($this->input));
        }
        return new Configuration(array());
    }

}