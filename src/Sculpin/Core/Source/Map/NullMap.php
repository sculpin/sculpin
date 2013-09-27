<?php

namespace Sculpin\Core\Source\Map;

use Sculpin\Core\Source\SourceInterface;

class NullMap implements MapInterface
{
    public function process(SourceInterface $source)
    {
        // NOOP
    }
}
