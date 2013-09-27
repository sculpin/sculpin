<?php

namespace Sculpin\Core\Source\Filter;

use Sculpin\Core\Source\SourceInterface;

class ChainFilter implements FilterInterface
{
    private $filters = array();

    public function __construct(array $filters = array())
    {
        $this->filters = $filters;
    }

    public function match(SourceInterface $source)
    {
        $matched = false;

        foreach ($this->filters as $filter) {
            if (!$filter->match($source)) {
                return false;
            }

            $matched = true;
        }

        return $matched;
    }

    public function addFilter(FilterInterface $filter)
    {
        $this->filters[] = $filter;
    }
}
