<?php

namespace Sculpin\Core\Source\Filter;

use Sculpin\Core\Source\SourceInterface;

class NullFilter implements FilterInterface
{
    public function match(SourceInterface $source)
    {
        return false;
    }
}
