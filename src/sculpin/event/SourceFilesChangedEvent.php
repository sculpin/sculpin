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

use sculpin\source\SourceFileSet;

use sculpin\Sculpin;

class SourceFilesChangedEvent extends Event {
    
    /**
     * Input files
     * @var \sculpin\SourceFileSet
     */
    protected $inputFiles;

    /**
     * Constructor
     * @param Sculpin $sculpin
     */
    public function __construct(Sculpin $sculpin, SourceFileSet $inputFileSet)
    {
        parent::__construct($sculpin);
        $this->inputFiles = $inputFileSet;
    }
    
    /**
     * Input files.
     * @return \sculpin\SourceFileSet
     */
    public function inputFiles()
    {
        return $this->inputFiles;
    }

}