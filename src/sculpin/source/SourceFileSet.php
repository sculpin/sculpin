<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin;

namespace sculpin\source;

class SourceFileSet
{
    
    /**
     * Newly added files
     * @var array
     */
    protected $newFiles = array();
    
    /**
     * Newly updated files
     * @var array
     */
    protected $updatedFiles = array();
    
    /**
     * Unchanged files
     * @var array
     */
    protected $unchangedFiles = array();

    /**
     * Constructor
     * @param array $newFiles
     * @param array $updatedFiles
     * @param array $unchangedFiles
     */
    public function __construct(array $newFiles, array $updatedFiles, array $unchangedFiles)
    {
        $this->newFiles = $newFiles;
        $this->updatedFiles = $updatedFiles;
        $this->unchangedFiles = $unchangedFiles;
    }

    /**
     * An array of newly added files
     * @return \sculpin\source\SourceFile[]
     */
    public function newFiles()
    {
        return $this->newFiles;
    }
    
    /**
     * An array of files that have been updated
     * @return \sculpin\source\SourceFile[]
     */
    public function updatedFiles()
    {
        return $this->updatedFiles;
    }
    
    /**
     * An array of files that are not changed
     * @return \sculpin\source\SourceFile[]
     */
    public function unchangedFiles()
    {
        return $this->unchangedFiles;
    }
    
    /**
     * An array of files that changed (contains all new and updated files)
     * @return \sculpin\source\SourceFile[]
     */
    public function changedFiles()
    {
        return array_merge($this->newFiles, $this->updatedFiles);
    }
    
    /**
     * An array of all files.
     * @return \sculpin\source\SourceFile[]
     */
    public function allFiles()
    {
        return array_merge($this->newFiles, $this->updatedFiles, $this->unchangedFiles);
    }

    /**
     * Has any file changed?
     * @return bool
     */
    public function hasChangedFiles()
    {
        return count($this->newFiles) or count($this->updatedFiles);
    }

}
