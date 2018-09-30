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

namespace Sculpin\Core\Source\Filter;

use Sculpin\Core\Source\SourceInterface;

class ChainFilter implements FilterInterface
{
    private $filters = [];
    private $or;

    public function __construct(array $filters = [], $or = false)
    {
        $this->filters = $filters;
        $this->or = $or;
    }

    public function match(SourceInterface $source): bool
    {
        $matched = false;

        foreach ($this->filters as $filter) {
            $matched = $filter->match($source);

            if ($matched) {
                if ($this->or) {
                    // If we would have accepted any filter ("or") we can
                    // return true at this point since at least one matched!
                    return true;
                }

                $matched = true;
            } else {
                if (! $this->or) {
                    // If we would not have accepted any filter ("and") we can
                    // return false at this point since at least one failed.
                    return false;
                }
            }
        }

        // We can assume this was either a case of no filters at all or all
        // filters matching.
        return $matched;
    }

    public function addFilter(FilterInterface $filter): void
    {
        $this->filters[] = $filter;
    }
}
