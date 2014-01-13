<?php

namespace Sculpin\Core\Source\Filter;

use Sculpin\Core\Source\Filter\FilterInterface;
use Sculpin\Core\Source\SourceInterface;

class MetaFilter implements FilterInterface
{
    private $key;
    private $value;

    public function __construct($key, $value) {
        $this->key = $key;
        $this->value = $value;
    }

    public function match(SourceInterface $source)
    {
        return $source->data()->get($this->key) === $this->value;
    }
}
