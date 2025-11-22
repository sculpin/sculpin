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

namespace Sculpin\Contrib\ProxySourceCollection\Sorter;

use Sculpin\Contrib\ProxySourceCollection\ProxySourceItem;

class DefaultSorter implements SorterInterface
{
    public function sort(ProxySourceItem $a, ProxySourceItem $b): int
    {
        return $a->date() && $a->title() && $b->date() && $b->title()
            ? strnatcmp($b->date() . ' ' . $b->title(), $a->date() . ' ' . $a->title())
            : strnatcmp($a->relativePathname(), $b->relativePathname());
    }
}
