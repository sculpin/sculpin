<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Event;

use Sculpin\Core\Source\SourceSet;

/**
 * Source Set Event.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class SourceSetEvent extends Event
{
    /**
     * Source Set
     *
     * @var SourceSet
     */
    protected $sourceSet;

    /**
     * Constructor.
     *
     * @param SourceSet $sourceSet Source set
     */
    public function __construct(SourceSet $sourceSet)
    {
        $this->sourceSet = $sourceSet;
    }

    /**
     * All sources
     *
     * @return array
     */
    public function allSources()
    {
        return $this->sourceSet->allSources();
    }

    /**
     * Updated sources
     *
     * @return array
     */
    public function updatedSources()
    {
        return $this->sourceSet->updatedSources();
    }

    /**
     * Current source set
     *
     * @return SourceSet
     */
    public function sourceSet()
    {
        return $this->sourceSet;
    }
}
