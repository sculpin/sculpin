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

use sculpin\source\ISource;

class SourceOutput implements IOutput
{
    
    /**
     * Source
     * 
     * @var ISource
     */
    protected $source;
    
    public function __construct(ISource $source)
    {
        $this->source = $source;
    }
    
    /**
     * @{inherit-doc}
     */
    public function outputId()
    {
        return $this->source->sourceId();
    }

    /**
     * @{inherit-doc}
     */
    public function pathname()
    {
        return $this->source->relativePathname();
    }
    
    /**
     * @{inherit-doc}
     */
    public function permalink()
    {
        return $this->source->permalink();
    }
    
    /**
     * @{inherit-doc}
     */
    public function hasFileReference()
    {
        return $this->source->useFileReference();
    }
    
    /**
     * @{inherit-doc}
     */
    public function file()
    {
        return $this->source->useFileReference()?$this->source->file():null;
    }
    
    /**
     * @{inherit-doc}
     */
    public function content()
    {
        return $this->source->useFileReference()?null:$this->source->content();
    }
}