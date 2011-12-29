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
     * YAML Configuration Filenames
     * @var array
     */
    private $yamlConfigurationFilenames;

    /**
     * Constructor
     * @param array $yamlConfigurationFilenames
     */
    public function __construct(array $yamlConfigurationFilenames)
    {
        $this->yamlConfigurationFilenames = $yamlConfigurationFilenames;
    }
    
    /**
     * {@inheritDoc}
     */
    public function build()
    {
        $config = array();
        foreach ( $this->yamlConfigurationFilenames as $yamlConfigurationFilename ) {
            if ( file_exists($yamlConfigurationFilename) ) {
                $config = Util::MERGE_ASSOC_ARRAY($config, Yaml::parse($yamlConfigurationFilename));
            }
        }
        return new Configuration($config);
    }

}