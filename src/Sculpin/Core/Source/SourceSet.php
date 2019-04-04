<?php

declare(strict_types=1);

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
 * @author Beau Simensen <beau@dflydev.com>
 */
class SourceSet
{
    /**
     * @var SourceInterface[]
     */
    protected $sources = [];

    /**
     * @var SourceInterface[]
     */
    protected $newSources = [];

    /**
     * @param SourceInterface[] $sources
     */
    public function __construct(array $sources = [])
    {
        foreach ($sources as $source) {
            $this->sources[$source->sourceId()] = $source;
        }
    }
    /**
     * Whether this set contains the specified source.
     */
    public function containsSource(SourceInterface $source): bool
    {
        return array_key_exists($source->sourceId(), $this->sources);
    }

    /**
     * Add this source to the list, tracking whether its a new or existing source.
     */
    public function mergeSource(SourceInterface $source): void
    {
        if (array_key_exists($source->sourceId(), $this->sources)) {
            unset($this->sources[$source->sourceId()]);
        } else {
            $this->newSources[$source->sourceId()] = $source;
        }
        $this->sources[$source->sourceId()] = $source;
    }

    /**
     * @return SourceInterface[]
     */
    public function allSources(): array
    {
        return $this->sources;
    }

    /**
     * All sources that have been changed.
     *
     * @return SourceInterface[]
     */
    public function updatedSources(): array
    {
        return array_filter($this->sources, function (SourceInterface $source) {
            return $source->hasChanged();
        });
    }

    /**
     * @return SourceInterface[]
     */
    public function newSources(): array
    {
        return $this->newSources;
    }

    /**
     * Reset all sources.
     *
     * Should be called after each loop while watching.
     */
    public function reset(): void
    {
        foreach ($this->sources as $source) {
            $source->setHasNotChanged();
        }

        $this->newSources = [];
    }
}
