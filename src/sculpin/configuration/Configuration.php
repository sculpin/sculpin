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

use Dflydev\DotAccessData\Data;
use Dflydev\PlaceholderResolver\PlaceholderResolverInterface;
use Dflydev\PlaceholderResolver\RegexPlaceholderResolver;

class Configuration
{
    protected $data;
    protected $placeholderResolver;
    protected $exportIsDirty = true;
    protected $resolvedExport;

    /**
     * Resolve values
     * 
     * For objects, do nothing. For strings, resolve placeholder.
     * For arrays, call resolveValues() on each item.
     */
    protected function resolveValues(&$input = null)
    {
        if (is_array($input)) {
            foreach ($input as $idx => $value) {
                $this->resolveValues($value);
                $input[$idx] = $value;
            }
        } else {
            if (!is_object($input)) {
                $input = $this->placeholderResolver->resolvePlaceholder($input);
            }
        }
    }

    /**
     * Constructor
     * 
     * @param array|null $config
     */    
    public function __construct(array $config = null, PlaceholderResolverInterface $placeholderResolver = null)
    {
        $this->data = new Data($config);
        $this->placeholderResolver = $placeholderResolver ?: new RegexPlaceholderResolver(new ConfigurationDataSource($this), '%', '%');
    }

    /**
     * Get a value (with placeholders unresolved)
     * 
     * @param string $key
     * @return mixed
     */
    public function getRaw($key)
    {
        return $this->data->get($key);
    }

    /**
     * Get a value (with placeholders resolved)
     * 
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $value = $this->getRaw($key);
        if (is_object($value)) {
            return $value;
        }
        $this->resolveValues($value);

        return $value;
    }

    /**
     * Set a value
     * 
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value = null)
    {
        $this->exportIsDirty = true;

        return $this->data->set($key, $value);
    }

    /**
     * Append a value
     * 
     * Will force key to be an array if it is only a string
     * 
     * @param string $key
     * @param mixed $value
     */
    public function append($key, $value = null)
    {
        $this->exportIsDirty = true;

        return $this->data->append($key, $value);
    }

    /**
     * Export configuration data as an associtaive array (with placeholders unresolved)
     * 
     * @return array
     */    
    public function exportRaw()
    {
        return $this->data->export();
    }

    /**
     * Export configuration data as an associtaive array (with placeholders resolved)
     * 
     * @return array
     */    
    public function export()
    {
        if ($this->exportIsDirty) {
            $this->resolvedExport = $this->data->export();
            $this->resolveValues($this->resolvedExport);
            $this->exportIsDirty = false;
        }

        return $this->resolvedExport;
    }

    /**
     * Get a sub Configuration instance
     * 
     * @return Configuration
     * @deprecated
     */
    public function getConfiguration($key)
    {
        $value = $this->get($key);
        if (is_array($value) && Util::IS_ASSOC($value)) {
            return new Configuration($value);
        }

        throw new \RuntimeException("Key $key is not suitable to be returned as a Configuration (is not an array)");
    }

    /**
     * Underlying Data representation
     * 
     * Will have all placeholders resolved.
     * 
     * @return Data
     */
    public function data()
    {
        return new Data($this->export());
    }

    /**
     * Import another Configuration
     * 
     * @param Configuration $imported
     * @param bool $clobber
     */    
    public function importRaw($imported, $clobber = true)
    {
        $this->exportIsDirty = true;

        $this->data->import($imported, $clobber);
    }
    /**
     * Import another Configuration
     * 
     * @param Configuration $imported
     * @param bool $clobber
     */    
    public function import(Configuration $imported, $clobber = true)
    {
        return $this->importRaw($imported->exportRaw(), $clobber);
    }

    /**
     * Get a the configuration value as a pathname from project root
     * 
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

    /**
     * Resolve placeholders in value from configuration
     * 
     * @param string|null $value
     * @return string
     */
    public function resolve($value = null)
    {
        if (null === $value) {
            return null;
        }

        return $this->placeholderResolver->resolvePlaceholder($value);
    }
}