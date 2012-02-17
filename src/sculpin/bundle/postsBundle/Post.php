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
     * Previous post
     * @var Post
     */
    protected $previousPost;

    /**
     * Next post
     * @var Post
     */
    protected $nextPost;

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
    
    public function id()
    {
        return $this->inputFile->id();
    }
    
    public function date()
    {
        return $this->inputFile->data()->get('calculatedDate');
    }
    
    public function meta()
    {
        return $this->inputFile->data()->export();
    }
    
    public function title()
    {
        return $this->inputFile->data()->get('title');
    }
    
    public function url()
    {
        return $this->inputFile->permalink()->relativeUrlPath();
    }
    
    public function blocks()
    {
        return $this->inputFile->data()->get('blocks');
    }

    /**
     * Previous post
     * @return \sculpin\bundle\postsBundle\Post
     */
    public function previousPost()
    {
        return $this->previousPost;
    }

    /**
     * Set previous post
     * @param Post $post
     */
    public function setPreviousPost(Post $post)
    {
        $this->previousPost = $post;
        $this->inputFile->data()->set('previousPost', $post);
    }

    /**
     * Next post
     * @return \sculpin\bundle\postsBundle\Post
     */
    public function nextPost()
    {
        return $this->nextPost;
    }

    /**
     * Set next post
     * @param Post $post
     */
    public function setNextPost(Post $post)
    {
        $this->nextPost = $post;
        $this->inputFile->data()->set('nextPost', $post);
    }

    /**
     * Post needs to be reprocessed
     */
    public function reprocess()
    {
        $this->inputFile->setHasChanged();
    }
    
    /**
     * Has the post changed?
     * @return boolean
     */
    public function hasChanged()
    {
        return $this->inputFile->hasChanged();
    }
}
