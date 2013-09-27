<?php

namespace Sculpin\Core\Source\Map;

use Sculpin\Core\Source\SourceInterface;

interface MapInterface
{
    public function process(SourceInterface $source);
}
