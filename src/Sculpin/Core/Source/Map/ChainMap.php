<?php

namespace Sculpin\Core\Source\Map;

use Sculpin\Core\Source\SourceInterface;

class ChainMap implements MapInterface
{
    private $maps = array();

    public function __construct(array $maps = array())
    {
        $this->maps = $maps;
    }

    public function process(SourceInterface $source)
    {
        foreach ($this->maps as $map) {
            $map->process($source);
        }
    }

    public function addMap(MapInterface $map)
    {
        $this->maps[] = $map;
    }
}
