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

/**
 * Posts Collection.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class Posts implements \ArrayAccess, \Iterator, \Countable
{
    /**
     * Posts
     * @var array
     */
    protected $posts;

    /**
     * Constructor
     *
     * @param array $posts
     */
    public function __construct(array $posts = null)
    {
        $this->posts = $posts !== null ? $posts : array();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->posts[] = $value;
        } else {
            $this->posts[$offset] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->posts[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->posts[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return isset($this->posts[$offset]) ? $this->posts[$offset] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        reset($this->posts);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->posts);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->posts);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        return next($this->posts);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->current() !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->posts);
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        uasort($this->posts, function ($a, $b) {
            return strnatcmp($b->date().' '.$b->title(), $a->date().' '.$a->title());
        });

        $previousPost = null;
        $post = null;

        foreach (array_reverse($this->posts) as $post) {
            if ($previousPost) {
                $previousPost->setNextPost($post);
            }
            $post->setPreviousPost($previousPost);
            $previousPost = $post;
        }

        if ($post) {
            $post->setNextPost(null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function first()
    {
        $keys = array_keys($this->posts);

        return $this->posts[$keys[0]];
    }
}
