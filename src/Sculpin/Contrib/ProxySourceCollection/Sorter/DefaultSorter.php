<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
