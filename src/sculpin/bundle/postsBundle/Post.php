<?php

/*
 * This file is a part of Sculpin.
 * 
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\bundle\postsBundle;

use sculpin\source\SourceFile;

use sculpin\Sculpin;

class Post
{
    /**
     * Input file
     * @var \sculpin\source\SourceFile
     */
    protected $inputFile;
    
    /**
     * Constructor
     * @param SourceFile $inputFile
     */
    public function __construct(SourceFile $inputFile)
    {
        $this->inputFile = $inputFile;
    }
    
    public function processBlocks(Sculpin $sculpin)
    {
        $blocks = $sculpin->formatBlocks($this->inputFile->id(), $this->inputFile->content(), $this->inputFile->context());
        $this->inputFile->data()->set('blocks', $blocks);
    }
    
}
