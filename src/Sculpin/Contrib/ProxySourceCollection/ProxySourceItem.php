<?php

namespace Sculpin\Contrib\ProxySourceCollection;

use Sculpin\Core\Source\ProxySource;

class ProxySourceItem extends ProxySource
{
    private $previousItem;
    private $nextItem;

    public function id()
    {
        return $this->sourceId();
    }

    public function meta()
    {
        return $this->data()->export();
    }

    public function url()
    {
        return $this->permalink()->relativeUrlPath();
    }

    public function blocks()
    {
        return $this->data()->get('blocks');
    }

    public function setBlocks(array $blocks = null)
    {
        $this->data()->set('blocks', $blocks ?: array());
    }

    public function previousItem()
    {
        return $this->previousItem;
    }

    public function setPreviousItem(ProxySourceItem $item = null)
    {
        $lastPreviousItem = $this->previousItem;
        $this->previousItem = $item;
        if ($lastPreviousItem) {
            // We did have a item before...
            if (!$item || $item->id() !== $lastPreviousItem->id()) {
                // But we no longer have a item or the item we
                // were given does not have the same ID as the
                // last one we had...
                $this->reprocess();
            }
        } elseif ($item) {
            // We didn't have a item before but we do now...
            $this->reprocess();
        }
    }

    public function nextItem()
    {
        return $this->nextItem;
    }

    public function setNextItem(ProxySourceItem $item = null)
    {
        $lastNextItem = $this->nextItem;
        $this->nextItem = $item;
        if ($lastNextItem) {
            // We did have a item before...
            if (!$item || $item->id() !== $lastNextItem->id()) {
                // But we no longer have a item or the item we
                // were given does not have the same ID as the
                // last one we had...
                $this->reprocess();
            }
        } elseif ($item) {
            // We didn't have a item before but we do now...
            $this->reprocess();
        }
    }

    public function reprocess()
    {
        $this->setHasChanged();
    }
}
