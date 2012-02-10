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

class Posts implements \ArrayAccess, \Iterator, \Countable
{
    
    /**
     * Posts
     * @var array
     */
    protected $posts;
    
    /**
     * Constructor
     * @param SourceFile $inputFile
     */
    public function __construct(array $posts = null)
    {
        $this->posts = $posts !== null ? $posts : array();
    }
    
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetSet()
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
     * (non-PHPdoc)
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return isset($this->posts[$offset]);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        unset($this->posts[$offset]);
    }
    
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        return isset($this->posts[$offset]) ? $this->posts[$offset] : null;
    }

    public function rewind()
    {
        reset($this->posts);
    }
    
    public function current()
    {
        return current($this->posts);
    }
    
    public function key()
    {
        return key($this->posts);
    }

    public function next()
    {
        return next($this->posts);
    }

    public function valid()
    {
        return $this->current() !== false;
    }
    
     public function count()
    {
        return count($this->posts);
    }


    public function init()
    {

        uasort($this->posts, function($a, $b) {
            /* @var $a Post */
            /* @var $b Post */
            return $a->date() < $b->date();
        });

        /* @var $previousPost Post */
        $previousPost = null;

        foreach (array_reverse($this->posts) as $post) {
            /* @var $post Post */
            if ($previousPost !== null) {
                $previousPost->setNextPost($post);
                $post->setPreviousPost($previousPost);
            }
            $previousPost = $post;
        }

    }

    public function first()
    {
        $keys = array_keys($this->posts);
        return $this->posts[$keys[0]];
    }

}
