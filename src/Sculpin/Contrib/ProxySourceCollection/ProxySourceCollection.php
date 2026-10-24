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

use ArrayAccess;
use Countable;
use Iterator;
use ReturnTypeWillChange;
use Sculpin\Contrib\ProxySourceCollection\Sorter\DefaultSorter;
use Sculpin\Contrib\ProxySourceCollection\Sorter\SorterInterface;

class ProxySourceCollection implements ArrayAccess, Iterator, Countable
{
    public function __construct(protected array $items = [], protected ?SorterInterface $sorter = null)
    {
        $this->sorter ??= new DefaultSorter;
    }

    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    #[ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    #[ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->items[$offset] ?? null;
    }

    #[ReturnTypeWillChange]
    public function rewind(): void
    {
        reset($this->items);
    }

    #[ReturnTypeWillChange]
    public function current()
    {
        return current($this->items);
    }

    #[ReturnTypeWillChange]
    public function key()
    {
        return key($this->items);
    }

    #[ReturnTypeWillChange]
    public function next(): void
    {
        next($this->items);
    }

    #[ReturnTypeWillChange]
    public function valid(): bool
    {
        return $this->current() !== false;
    }

    #[ReturnTypeWillChange]
    public function count(): int
    {
        return count($this->items);
    }

    public function init(): void
    {
        $this->sort();

        /**
         * @var ProxySourceItem|null $item
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
            $item->setNextItem();
        }
    }

    public function first()
    {
        $keys = array_keys($this->items);

        return $this->items[$keys[0]];
    }

    /**
     * Sorts proxy source items using the StableSort algorithm from Martijn van der Lee
     *
     * See: https://github.com/vanderlee/PHP-stable-sort-functions
     */
    public function sort(): void
    {
        $index      = 0;
        $comparator = [$this->sorter, 'sort'];

        // add an index to provide stable sorting
        foreach ($this->items as &$item) {
            $item = [$index++, $item];
        }

        unset($item);

        uasort($this->items, function ($a, $b) use ($comparator) {
            $result = $comparator($a[1], $b[1]);

            // use the index to prevent undefined behaviour when comparator reports items are "equal"
            return $result === 0 ? $a[0] - $b[0] : $result;
        });

        // remove the index
        foreach ($this->items as &$item) {
            $item = $item[1];
        }

        unset($item);
    }
}
