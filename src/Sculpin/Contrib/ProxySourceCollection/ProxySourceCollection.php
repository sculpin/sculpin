<?php

declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Contrib\ProxySourceCollection;

use Sculpin\Contrib\ProxySourceCollection\Sorter\DefaultSorter;
use Sculpin\Contrib\ProxySourceCollection\Sorter\SorterInterface;
use StableSort\StableSort;

class ProxySourceCollection implements \ArrayAccess, \Iterator, \Countable
{
    /**
     * @var $items ProxySourceItem[]
     */
    protected $items = [];
    protected $sorter;

    public function __construct(array $items = [], SorterInterface $sorter = null)
    {
        $this->items = $items;
        $this->sorter = $sorter ?: new DefaultSorter;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    public function rewind()
    {
        reset($this->items);
    }

    public function current()
    {
        return current($this->items);
    }

    public function key()
    {
        return key($this->items);
    }

    public function next()
    {
        return next($this->items);
    }

    public function valid()
    {
        return $this->current() !== false;
    }

    public function count()
    {
        return count($this->items);
    }

    public function init()
    {
        $this->sort();

        /**
         * @var $item ProxySourceItem|null
         */
        $item = null;

        foreach (array_reverse($this->items) as $currItem) {
            if ($item) {
                $item->setNextItem($currItem);
            }
            $currItem->setPreviousItem($item);
            $item = $currItem;
        }

        if ($item) {
            $item->setNextItem(null);
        }
    }

    public function first()
    {
        $keys = array_keys($this->items);

        return $this->items[$keys[0]];
    }

    public function sort()
    {
        StableSort::uasort($this->items, [$this->sorter, 'sort']);
    }
}
