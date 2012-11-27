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
use Sculpin\Core\Source\ProxySource;

/**
 * Post.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class Post extends ProxySource
{
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
     * ID
     *
     * @return string
     */
    public function id()
    {
        return $this->sourceId();
    }

    /**
     * Date
     *
     * @return string
     */
    public function date()
    {
        return $this->data()->get('calculatedDate');
    }

    /**
     * Meta
     *
     * @return array
     */
    public function meta()
    {
        return $this->data()->export();
    }

    /**
     * Title
     *
     * @return string
     */
    public function title()
    {
        return $this->data()->get('title');
    }

    /**
     * URL
     *
     * @return string
     */
    public function url()
    {
        return $this->permalink()->relativeUrlPath();
    }

    /**
     * Blocks
     *
     * @return array
     */
    public function blocks()
    {
        return $this->data()->get('blocks');
    }

    /**
     * Set Blocks
     *
     * @param array $blocks
     */
    public function setBlocks(array $blocks = null)
    {
        $this->data()->set('blocks', $blocks ?: array());
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
        $this->data()->set('previousPost', $post);
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
        $this->data()->set('nextPost', $post);
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
        $this->setHasChanged();
    }
}
