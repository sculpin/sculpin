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

class Configuration {
    
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    public function get($key)
    {
        $currentValue = $this->config;
        $keyPath = explode('.', $key);
        for ( $i = 0; $i < count($keyPath); $i++ ) {
            $currentKey = $keyPath[$i];
            if ( ! isset($currentValue[$currentKey]) ) { return null; }
            $currentValue = $currentValue[$currentKey];
        }
        return $currentValue;
    }

    public function set($key, $value)
    {
        $currentValue =& $this->config;
        $keyPath = explode('.', $key);
        if (count($keyPath)==0) {
            throw new \RuntimeException("key cannot be an empty string");
        }
        if (count($keyPath)==1) {
            $currentValue[$key] = $value;
            return;
        }
        $endKey = array_pop($keyPath);
        for ( $i = 0; $i < count($keyPath); $i++ ) {
            $currentKey =& $keyPath[$i];
            if ( ! isset($currentValue[$currentKey]) ) {
                $currentValue[$currentKey] = array();
            }
            $currentValue =& $currentValue[$currentKey];
        }
        $currentValue[$endKey] = $value;
    }

    public function append($key, $value)
    {
        $currentValue =& $this->config;
        $keyPath = explode('.', $key);
        if (count($keyPath)==0) {
            throw new \RuntimeException("key cannot be an empty string");
        }
        if (count($keyPath)==1) {
            if (!isset($currentValue[$key])) {
                $currentValue[$key] = array();
            }
            if (!is_array($currentValue[$key])) {
                $currentValue[$key] = array($currentValue[$key]);
            }
            $currentValue[$key][] = $value;
            return;
        }
        $endKey = array_pop($keyPath);
        for ( $i = 0; $i < count($keyPath); $i++ ) {
            $currentKey =& $keyPath[$i];
            if ( ! isset($currentValue[$currentKey]) ) {
                $currentValue[$currentKey] = array();
            }
            $currentValue =& $currentValue[$currentKey];
        }
        if(!isset($currentValue[$endKey])) {
            $currentValue[$endKey] = array();
        }
        if (!is_array($currentValue[$endKey])) {
            $currentValue[$endKey] = array($currentValue[$endKey]);
        }
        $currentValue[$endKey][] = $value;
    }
    
    public function export() {
        return $this->config;
    }
    
    public function getPath($key)
    {
        $path = $this->get($key);
        if ('/' == $path[0]) {
            return $path;
        }
        if ('.' == $path) {
            return $this->get('project_root') ?: getcwd();
        }
        return ($this->get('project_root') ?: getcwd()).'/'.$path;
    }

    public function getConfiguration($key)
    {
        $value = $this->get($key);
        if (Util::IS_ASSOC($value)) {
            return new Configuration($value);
        }
        // TODO: This should probably throw an exception?
        return $value;
    }
    
    public function import(Configuration $imported, $clobber = true)
    {
        $this->config = Util::MERGE_ASSOC_ARRAY($this->config, $imported->export(), $clobber);
    }

}