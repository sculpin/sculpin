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

class Configuration {
    
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    public function get($key) {
        $currentValue = $this->config;
        $keyPath = explode('.', $key);
        for ( $i = 0; $i < count($keyPath); $i++ ) {
            $currentKey = $keyPath[$i];
            if ( ! isset($currentValue[$currentKey]) ) { return null; }
            $currentValue = $currentValue[$currentKey];
        }
        return $currentValue;
    }

    public function export() {
        return $this->config;
    }

}