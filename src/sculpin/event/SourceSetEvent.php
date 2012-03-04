<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\event;

use sculpin\source\SourceSet;
use sculpin\Sculpin;

class SourceSetEvent extends Event
{
    /**
     * Source set
     * 
     * @var \sculpin\source\SourceSet
     */
    protected $sourceSet;

    /**
     * Constructor
     * 
     * @param Sculpin $sculpin
     * @param SourceSet $sourceSet
     */
    public function __construct(Sculpin $sculpin, SourceSet $sourceSet)
    {
        parent::__construct($sculpin);
        $this->sourceSet = $sourceSet;
    }

    /**
     * All sources
     * 
     * @return \sculpin\source\ISource[]
     */
    public function allSources()
    {
        return $this->sourceSet->allSources();
    }

    /**
     * Updated sources
     * 
     * @return \sculpin\source\ISource[]
     */
    public function updatedSources()
    {
        return $this->sourceSet->updatedSources();
    }
}