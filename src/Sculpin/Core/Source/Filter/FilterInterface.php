<?php

namespace Sculpin\Core\Source\Filter;

use Sculpin\Core\Source\SourceInterface;

interface FilterInterface
{
    public function match(SourceInterface $source);
}
