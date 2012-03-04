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

use sculpin\source\ISource;

use sculpin\Sculpin;

class Post
{
    /**
     * Input file
     * @var \sculpin\source\ISource
     */
    protected $source;
    
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
     * @param ISource $source
     */
    public function __construct(ISource $source)
    {
        $this->source = $source;
    }
    
    public function processBlocks(Sculpin $sculpin)
    {
        $blocks = $sculpin->formatBlocks($this->source->sourceId(), $this->source->content(), $this->source->data()->export());
        $this->source->data()->set('blocks', $blocks);
    }
    
    public function id()
    {
        return $this->source->sourceId();
    }
    
    public function date()
    {
        return $this->source->data()->get('calculatedDate');
    }
    
    public function meta()
    {
        return $this->source->data()->export();
    }
    
    public function title()
    {
        return $this->source->data()->get('title');
    }
    
    public function url()
    {
        return $this->source->permalink()->relativeUrlPath();
    }
    
    public function blocks()
    {
        return $this->source->data()->get('blocks');
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
        $this->source->data()->set('previousPost', $post);
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
        $this->source->data()->set('nextPost', $post);
    }

    /**
     * Post needs to be reprocessed
     */
    public function reprocess()
    {
        $this->source->setHasChanged();
    }
    
    /**
     * Has the post changed?
     * @return boolean
     */
    public function hasChanged()
    {
        return $this->source->hasChanged();
    }
}
