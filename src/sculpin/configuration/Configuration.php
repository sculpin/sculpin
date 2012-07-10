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

use Dflydev\DotAccessConfiguration\Configuration as BaseConfiguration;
use Dflydev\DotAccessData\Util as DotAccessDataUtil;

class Configuration extends BaseConfiguration
{
    /**
     * Get a sub Configuration instance
     * 
     * @param string $key
     * @return Configuration
     * @deprecated
     */
    public function getConfiguration($key)
    {
        $value = $this->get($key);
        if (is_array($value) && DotAccessDataUtil::isAssoc($value)) {
            return new Configuration($value);
        }

        throw new \RuntimeException("Key $key is not suitable to be returned as a Configuration (is not an array)");
    }

    /**
     * Get a the configuration value as a pathname from project root
     * 
     * @param string $key
     * @return string
     */
    public function getPath($key)
    {
        $path = $this->get($key);

        if ('/' === $path[0]) {
            return $path;
        }

        if ('.' === $path) {
            return $this->get('project_root') ?: getcwd();
        }

        return ($this->get('project_root') ?: getcwd()).'/'.$path;
    }
}
