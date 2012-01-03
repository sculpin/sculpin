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

use sculpin\Sculpin;

class InputFilesChangedEvent extends Event {
    
    public $newFiles = array();
    public $updatedFiles = array();
    public $unchangedFiles = array();

    /**
     * Constructor
     * @param Sculpin $sculpin
     */
    public function __construct(Sculpin $sculpin, array $newFiles, array $updatedFiles, array $unchangedFiles)
    {
        parent::__construct($sculpin);
        $this->newFiles = $newFiles;
        $this->updatedFiles = $updatedFiles;
        $this->unchangedFiles = $unchangedFiles;
    }

}