<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\source;

class SourceSet
{
    /**
     * Sources
     * 
     * @var Array
     */
    protected $sources = array();

    /**
     * Constructor
     * 
     * @param Array $sources
     */
    public function __construct(array $sources = array())
    {
        foreach ($sources as $source) {
            $this->sources[$source->id()] = $source;
        }
    }

    /**
     * Set contains the source?
     * 
     * @param ISource $source
     * @return boolean
     */
    public function containsSource(ISource $source)
    {
        return array_key_exists($source->sourceId(), $this->sources);
    }

    /**
     * Merge a source
     * 
     * @param ISource $source
     */
    public function mergeSource(ISource $source)
    {
        if (array_key_exists($source->sourceId(), $this->sources)) {
            unset($this->sources[$source->sourceId()]);
        }
        $this->sources[$source->sourceId()] = $source;
    }

    /**
     * All sources
     * 
     * @return \sculpin\source\ISource[]
     */
    public function allSources()
    {
        return $this->sources;
    }
    
    /**
     * All sources that have been updated
     * 
     * @return \sculpin\source\ISource[]
     */
    public function updatedSources()
    {
        return array_filter($this->sources, function(ISource $source) { return $source->hasChanged(); });
    }

    /**
     * Set has updated sources. SLOW.
     * 
     * @return Boolean
     */
    public function hasUpdatedSources()
    {
        return count($this->updatedSources()) > 0;
    }

    /**
     * Reset all sources
     * 
     * Should be called after each loop while watching.
     */
    public function reset()
    {
        foreach ($this->sources as $source) {
            $source->setHasNotChanged();
        }
    }
}