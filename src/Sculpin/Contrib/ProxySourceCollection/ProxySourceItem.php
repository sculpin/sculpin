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
use Dflydev\DotAccessData\DataInterface;
use InvalidArgumentException;
use Override;
use ReturnTypeWillChange;
use Sculpin\Core\Source\ProxySource;

class ProxySourceItem extends ProxySource implements ArrayAccess
{
    private ?ProxySourceItem $previousItem = null;
    private ?ProxySourceItem $nextItem = null;

    public function id(): string
    {
        return $this->sourceId();
    }

    public function meta(): array
    {
        return $this->data()->export();
    }

    #[Override]
    public function url(): string
    {
        return $this->permalink()->relativeUrlPath();
    }

    public function date()
    {
        return $this->data()->get('calculated_date') ?: $this->data()->get('date');
    }

    public function title()
    {
        return $this->data()->get('title');
    }

    public function blocks()
    {
        return $this->data()->get('blocks');
    }

    public function setBlocks(?array $blocks = null): void
    {
        $this->data()->set('blocks', $blocks ?: []);
    }

    public function previousItem(): ?ProxySourceItem
    {
        return $this->previousItem;
    }

    public function setPreviousItem(?ProxySourceItem $item = null): void
    {
        $lastPreviousItem = $this->previousItem ?? null;
        $this->previousItem = $item;
        if ($lastPreviousItem instanceof ProxySourceItem) {
            // We did have an item before...
            if (!$item instanceof ProxySourceItem || $item->id() !== $lastPreviousItem->id()) {
                // But we no longer have an item or the item we
                // were given does not have the same ID as the
                // last one we had...
                $this->reprocess();
            }
        } elseif ($item instanceof ProxySourceItem) {
            // We didn't have an item before, but we do now...
            $this->reprocess();
        }
    }

    public function nextItem(): ?ProxySourceItem
    {
        return $this->nextItem;
    }

    public function setNextItem(?ProxySourceItem $item = null): void
    {
        $lastNextItem = $this->nextItem ?? null;
        $this->nextItem = $item;

        if ($lastNextItem instanceof ProxySourceItem) {
            // We did have an item before...
            if (!$item instanceof ProxySourceItem || $item->id() !== $lastNextItem->id()) {
                // But we no longer have an item or the item we
                // were given does not have the same ID as the
                // last one we had...
                $this->reprocess();
            }
        } elseif ($item instanceof ProxySourceItem) {
            // We didn't have an item before, but we do now...
            $this->reprocess();
        }
    }

    public function reprocess(): void
    {
        $this->setHasChanged();
    }

    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            throw new InvalidArgumentException('Proxy source items cannot have values pushed onto them');
        }

        if (method_exists($this, $offset)) {
            call_user_func([$this, $offset], $value);
        }

        $setMethod = 'set'.ucfirst((string) $offset);
        if (method_exists($this, $setMethod)) {
            call_user_func([$this, $setMethod], $value);
        }

        $this->data()->set($offset, $value);
    }

    #[ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return ! method_exists($this, $offset) && null !== $this->data()->get($offset);
    }

    #[ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        if (! method_exists($this, $offset)) {
            $data = $this->data();
            if ($data instanceof DataInterface) {
                $data->remove($offset);
            }
        }
    }

    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (method_exists($this, $offset)) {
            return call_user_func([$this, $offset]);
        }

        return $this->data()->get($offset);
    }
}
