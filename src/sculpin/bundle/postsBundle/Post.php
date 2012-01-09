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
        $blocks = $sculpin->formatBlocks($this->inputFile->content(), $this->inputFile->context());
        $this->inputFile->data()->set('blocks', $blocks);

        /**
        $this->inputFile->modifyContext('post', array_merge(
            $this->inputFile->context(),
            array('blocks' => $blocks,)
        ));
        */
        /*
        $context = $this->inputFile->context();
        $blocks = array();
        if ($count = preg_match_all('/{%\s+block\s+(\w+)\s+%}(.*?){%\s+endblock\s+%}/si', $this->inputFile->content(), $matches)) {
            for ($i = 0; $i < $count; $i++ ) {
                $blocks[$matches[1][$i]] = $sculpin->formatPage($matches[2][$i], array('page' => $context, 'post' => $context));
            }
        }
        $this->inputFile->modifyContext('blocks', $blocks);
        */
        /*
        $this->inputFile->modifyContext('post', array_merge(
            $this->inputFile->context(),
            array('blocks' => $blocks,)
        ));
        */
    }
    
}
