<?php

namespace Sculpin\Contrib\ProxySourceCollection;

class ProxySourceCollection implements \ArrayAccess, \Iterator, \Countable
{
    protected $items;

    public function __construct(array $items = array())
    {
        $this->items = $items;
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
        $previousItem = null;
        $item = null;

        foreach (array_reverse($this->items) as $item) {
            if ($previousItem) {
                $previousItem->setNextItem($item);
            }
            $item->setPreviousItem($previousItem);
            $previousItem = $item;
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
}
