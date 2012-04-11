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
        return $this->placeholderResolver->resolvePlaceholder($this->getRaw($key));
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
    protected function resolveValues(&$input = null)
    {
        if (is_array($input)) {
            foreach ($input as $value) {
                $this->resolveValues($value);
            }
        } else {
            //
        }
        //
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
        // TODO: This should probably throw an exception?
        return $value;
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