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

namespace Sculpin\Core\Source\Map;

use Sculpin\Core\Source\SourceInterface;

class ChainMap implements MapInterface
{
    private $maps = [];

    public function __construct(array $maps = [])
    {
        $this->maps = $maps;
    }

    public function process(SourceInterface $source): void
    {
        foreach ($this->maps as $map) {
            $map->process($source);
        }
    }

    public function addMap(MapInterface $map): void
    {
        $this->maps[] = $map;
    }
}
