<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\PostsBundle;

use Sculpin\Core\Source\SourceInterface;

/**
 * Post.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class Post
{
    /**
     * Source
     *
     * @var SourceInterface
     */
    protected $source;

    /**
     * Previous post
     *
     * @var Post
     */
    protected $previousPost;

    /**
     * Next post
     *
     * @var Post
     */
    protected $nextPost;

    /**
     * Constructor
     *
     * @param SourceInterface $source
     */
    public function __construct(SourceInterface $source)
    {
        $this->source = $source;
    }

    /**
     * Source
     *
     * @return SourceInterface
     */
    public function source()
    {
        return $this->source;
    }

    /**
     * ID
     *
     * @return string
     */
    public function id()
    {
        return $this->source->sourceId();
    }

    /**
     * Date
     *
     * @return string
     */
    public function date()
    {
        return $this->source->data()->get('calculatedDate');
    }

    /**
     * Meta
     *
     * @return array
     */
    public function meta()
    {
        return $this->source->data()->export();
    }

    /**
     * Title
     *
     * @return string
     */
    public function title()
    {
        return $this->source->data()->get('title');
    }

    /**
     * URL
     *
     * @return string
     */
    public function url()
    {
        return $this->source->permalink()->relativeUrlPath();
    }

    /**
     * Blocks
     *
     * @return array
     */
    public function blocks()
    {
        return $this->source->data()->get('blocks');
    }

    /**
     * Set Blocks
     *
     * @param array $blocks
     */
    public function setBlocks(array $blocks = null)
    {
        $this->source->data()->set('blocks', $blocks ?: array());
    }

    /**
     * Previous Post
     *
     * @return Post
     */
    public function previousPost()
    {
        return $this->previousPost;
    }

    /**
     * Set previous Post
     *
     * @param Post $post
     */
    public function setPreviousPost(Post $post = null)
    {
        $lastPreviousPost = $this->previousPost;
        $this->previousPost = $post;
        $this->source->data()->set('previousPost', $post);
        if ($lastPreviousPost) {
            // We did have a post before...
            if (!$post || $post->id() !== $lastPreviousPost->id()) {
                // But we no longer have a post or the post we
                // were given does not have the same ID as the
                // last one we had...
                $this->reprocess();
            }
        } elseif ($post) {
            // We didn't have a post before but we do now...
            $this->reprocess();
        }
    }

    /**
     * Next Post
     *
     * @return Post
     */
    public function nextPost()
    {
        return $this->nextPost;
    }

    /**
     * Set next Post
     *
     * @param Post $post
     */
    public function setNextPost(Post $post = null)
    {
        $lastNextPost = $this->nextPost;
        $this->nextPost = $post;
        $this->source->data()->set('nextPost', $post);
        if ($lastNextPost) {
            // We did have a post before...
            if (!$post || $post->id() !== $lastNextPost->id()) {
                // But we no longer have a post or the post we
                // were given does not have the same ID as the
                // last one we had...
                $this->reprocess();
            }
        } elseif ($post) {
            // We didn't have a post before but we do now...
            $this->reprocess();
        }
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
     *
     * @return boolean
     */
    public function hasChanged()
    {
        return $this->source->hasChanged();
    }
}
