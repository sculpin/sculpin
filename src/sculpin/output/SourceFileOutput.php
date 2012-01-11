<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\output;

use sculpin\source\SourceFile;

class SourceFileOutput implements IOutput
{
    
    /**
     * Source file
     * @var SourceFile
     */
    protected $sourceFile;
    
    public function __construct(SourceFile $sourceFile)
    {
        $this->sourceFile = $sourceFile;
    }
    
    /**
     * (non-PHPdoc)
     * @see sculpin\output.IOutput::outputId()
     */
    public function outputId()
    {
        return $this->sourceFile->id();
    }
    
    public function pathname()
    {
        return $this->sourceFile->file()->getRelativePathname();
    }
    
    public function canHavePermalink()
    {
        return $this->sourceFile->canBeProcessed();
    }
    
    public function permalink()
    {
        return $this->sourceFile->data()->get('permalink');
    }
    
    public function hasFileReference()
    {
        return !$this->sourceFile->canBeProcessed();
    }
    
    public function file()
    {
        return $this->sourceFile->canBeProcessed()?null:$this->sourceFile->file();
    }
    
    public function content()
    {
        return $this->sourceFile->canBeProcessed()?$this->sourceFile->content():null;
    }

}