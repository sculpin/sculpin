<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Source;

/**
 * Source Set.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class SourceSet
{
    /**
     * Sources
     *
     * @var array
     */
    protected $sources = array();

    /**
     * New Sources
     *
     * @var array
     */
    protected $newSources = array();

    /**
     * Constructor.
     *
     * @param array $sources
     */
    public function __construct(array $sources = array())
    {
        foreach ($sources as $source) {
            $this->sources[$source->sourceId()] = $source;
        }
    }
    /**
     * Set contains the source?
     *
     * @param SourceInterface $source
     *
     * @return boolean
     */
    public function containsSource(SourceInterface $source)
    {
        return array_key_exists($source->sourceId(), $this->sources);
    }

    /**
     * Merge a source
     *
     * @param SourceInterface $source
     */
    public function mergeSource(SourceInterface $source)
    {
        if (array_key_exists($source->sourceId(), $this->sources)) {
            unset($this->sources[$source->sourceId()]);
        } else {
            $this->newSources[$source->sourceId()] = $source;
        }
        $this->sources[$source->sourceId()] = $source;
    }

    /**
     * All sources
     *
     * @return array
     */
    public function allSources()
    {
        return $this->sources;
    }

    /**
     * All sources that have been updated
     *
     * @return array
     */
    public function updatedSources()
    {
        return array_filter($this->sources, function (SourceInterface $source) {
            return $source->hasChanged();
        });
    }

    public function newSources()
    {
        return $this->newSources;
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

        $this->newSources = array();
    }
}
