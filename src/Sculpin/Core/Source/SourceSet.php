<?php declare(strict_types=1);

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
    protected $sources = [];

    /**
     * New Sources
     *
     * @var array
     */
    protected $newSources = [];

    /**
     * Constructor.
     */
    public function __construct(array $sources = [])
    {
        foreach ($sources as $source) {
            $this->sources[$source->sourceId()] = $source;
        }
    }
    /**
     * Set contains the source?
     */
    public function containsSource(SourceInterface $source): bool
    {
        return array_key_exists($source->sourceId(), $this->sources);
    }

    /**
     * Merge a source
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
     * All sources
     */
    public function allSources(): array
    {
        return $this->sources;
    }

    /**
     * All sources that have been updated
     */
    public function updatedSources(): array
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
    public function reset(): void
    {
        foreach ($this->sources as $source) {
            $source->setHasNotChanged();
        }

        $this->newSources = [];
    }
}
