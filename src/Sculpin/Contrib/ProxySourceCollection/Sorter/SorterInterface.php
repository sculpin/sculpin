<?php

namespace Sculpin\Contrib\ProxySourceCollection\Sorter;

use Sculpin\Contrib\ProxySourceCollection\ProxySourceItem;

interface SorterInterface
{
    public function sort(ProxySourceItem $a, ProxySourceItem $b);
}
