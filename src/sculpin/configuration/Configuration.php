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
        $this->placeholderResolver = $placeholderResolver ?: new RegexPlaceholderResolver(new ConfigurationDataSource($this));
    }

    public function getRaw($key)
    {
        return $this->data->get($key);
    }

    public function get($key)
    {
        $value = $this->getRaw($key);
        if (is_object($value)) {
            return $value;
        }
        $this->resolveValues($value);
        return $value;
    }

    public function set($key, $value = null)
    {
        return $this->data->set($key, $value);
    }

    public function append($key, $value = null)
    {
        return $this->data->append($key, $value);
    }
    
    public function export() {
        if ($this->exportIsDirty) {
            $this->resolvedExport = $this->data->export();
            $this->resolveValues($this->resolvedExport);
            $this->exportIsDirty = false;
        }
        return $this->resolvedExport;
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
        if (is_array($value) && Util::IS_ASSOC($value)) {
            return new Configuration($value, $this->placeholderResolver);
        }
        throw new \RuntimeException("Key $key is not suitable to be returned as a Configuration (is not an array)");
    }

    /**
     * Import another Configuration
     * 
     * @param Configuration $imported
     * @param bool $clobber
     */    
    public function import(Configuration $imported, $clobber = true)
    {
        $this->data->import($imported->export(), $clobber);
    }
}