<?php

namespace Sculpin\Contrib\ProxySourceCollection\Sorter;

use Sculpin\Contrib\ProxySourceCollection\ProxySourceItem;

class DefaultSorter implements SorterInterface
{
    public function sort(ProxySourceItem $a, ProxySourceItem $b)
    {
        if ($a->date() && $a->title() && $b->date() && $b->title()) {
            return strnatcmp($b->date().' '.$b->title(), $a->date().' '.$a->title());
        }

        return strnatcmp($a->relativePathname(), $b->relativePathname());
    }
}
