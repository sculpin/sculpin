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
    protected array $sources = [];

    /**
     * @var SourceInterface[]
     */
    protected array $newSources = [];

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
     * Add this source to the list, tracking whether it is a new or existing source.
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
        return array_filter($this->sources, fn(SourceInterface $source): bool => $source->hasChanged());
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

    /**
     * Sorts the internal sources and newSources arrays
     *
     * When no sort function is provided, dataAwareSortHelper is used.
     *
     * @param callable|null $sortFunction   Default is $this->dataAwareSortHelper()
     * @return void
     */
    public function sort(?callable $sortFunction = null): void
    {
        $sortFunction ??= [$this, 'dataAwareSortHelper'];

        uasort($this->sources, $sortFunction);
        uasort($this->newSources, $sortFunction);
    }

    /**
     * Digs into the provided sources to sort them according to:
     *
     * - frontmatter 'use:' array
     * - frontmatter 'generator:' string
     * - isRaw state (e.g., image files will be isRaw but markdown files will not)
     * - sourceId / filename
     *
     * This puts items with no "use:" statements first in line for processing,
     * which will ensure data providers and generators have full access to
     * formatted content blocks in the files they depend on.
     *
     * @param SourceInterface $a
     * @param SourceInterface $b
     * @return int      A sort value (-1, 0, 1) provided by the UFO operator
     */
    protected function dataAwareSortHelper(SourceInterface $a, SourceInterface $b): int
    {
        if ($a === $b) {
            return 0;
        }

        $nameCheck = $a->sourceId() <=> $b->sourceId();
        $rawCheck = $a->isRaw() <=> $b->isRaw();

        // Examine the "use:" frontmatter data value, usually an array (or not present)
        $aUses = $a->data()->get('use');
        $bUses = $b->data()->get('use');
        $usesCheck = $aUses <=> $bUses;

        // Examine the "generator:" frontmatter data value, usually a string (or not present)
        $aGenerator = $a->data()->get('generator');
        $bGenerator = $b->data()->get('generator');
        $generatorCheck = $aGenerator <=> $bGenerator;

        return match (true) {
            0 !== $usesCheck => $usesCheck,
            0 !== $generatorCheck => $generatorCheck,
            0 !== $rawCheck => $rawCheck,
            0 !== $nameCheck => $nameCheck,
            default => 0,
        };
    }
}
