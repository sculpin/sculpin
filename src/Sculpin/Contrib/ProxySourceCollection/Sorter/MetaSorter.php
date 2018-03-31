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

class MetaSorter implements SorterInterface
{
    private $key;
    private $reversed;

    public function __construct($key = null, $direction = 'desc')
    {
        $this->setKey($key);
        $this->setReversed($direction);
    }

    private function setKey($key = null)
    {
        if (null === $key) {
            throw new \InvalidArgumentException('Key must be specified');
        }

        $this->key = $key;
    }
    private function setReversed($direction)
    {
        switch (strtolower($direction)) {
            case 'asc':
            case 'ascending':
                $this->reversed = true;
                break;
            case 'desc':
            case 'descending':
                $this->reversed = false;
                break;
            default:
                throw new \InvalidArgumentException(
                    'Invalid value passed for direction, must be one of: asc, ascending, desc, descending'
                );
        }
    }

    public function sort(ProxySourceItem $a, ProxySourceItem $b)
    {
        if ($this->reversed) {
            return strnatcmp($b[$this->key], $a[$this->key]);
        }

        return strnatcmp($a[$this->key], $b[$this->key]);
    }
}
