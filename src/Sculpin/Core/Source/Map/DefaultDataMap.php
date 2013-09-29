<?php

namespace Sculpin\Core\Source\Map;

use Sculpin\Core\Source\SourceInterface;

class DefaultDataMap implements MapInterface
{
    private $defaults;

    public function __construct(array $defaults = array())
    {
        $this->defaults = $defaults;
    }

    public function process(SourceInterface $source)
    {
        foreach ($this->defaults as $name => $value) {
            if (!$source->data()->get($name) && $value) {
                $source->data()->set($name, $value);
            }
        }
    }
}
