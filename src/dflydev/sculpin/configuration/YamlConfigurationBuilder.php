<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dflydev\sculpin\configuration;

use Symfony\Component\Yaml\Yaml;

class YamlConfigurationBuilder {
    
    /**
     * YAML Configuration Filenames
     * @var array
     */
    private $yamlConfigurationFilenames;

    public function __construct(array $yamlConfigurationFilenames)
    {
        $this->yamlConfigurationFilenames = $yamlConfigurationFilenames;
    }
    
    /**
     * Build a configuration
     * @return array
     */
    public function build()
    {
        $config = array();
        foreach ( $this->yamlConfigurationFilenames as $yamlConfigurationFilename ) {
            if ( file_exists($yamlConfigurationFilename) ) {
                echo 'Including "' . $yamlConfigurationFilename . '"' . "\n";
                $config = self::MERGE($config, Yaml::parse($yamlConfigurationFilename));
            } else {
                echo 'Skipping "' . $yamlConfigurationFilename . '" (missing)' . "\n";
            }
        }
        return $config;
    }

    /**
     * Test if array is an associative array
     * 
     * Note that this function will return true if an array is empty. Meaning
     * empty arrays will be treated as if they are associative arrays.
     * 
     * @param array $arr
     * @return boolean
     */
    static private function IS_ASSOC(array $arr)
    {
        return (is_array($arr) && (!count($arr) || count(array_filter(array_keys($arr),'is_string')) == count($arr)));
    }

    /**
     * Merge the contents of one thingy into another thingy
     * @param mixed $to
     * @param mixed $from
     */
    static private function MERGE($to, $from)
    {
        if ( is_array($from) ) {
            foreach ( $from as $k => $v ) {
                if ( ! isset($to[$k]) ) { $to[$k] = $v; }
                else {
                    $to[$k] = self::MERGE($to[$k], $v);
                }
            }
            return $to;
        }
        return $from;
    }

}