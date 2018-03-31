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

namespace Sculpin\Contrib\Taxonomy;

use Sculpin\Contrib\Taxonomy\PermalinkStrategy\PermalinkStrategyInterface;

class PermalinkStrategyCollection
{
    protected $strategies;

    public function __construct()
    {
        $this->strategies = new \SplObjectStorage();
    }

    public function push(PermalinkStrategyInterface $strategy)
    {
        $this->strategies->attach($strategy);
    }

    public function process($str)
    {
        foreach ($this->strategies as $strategy) {
            $str = $strategy->process($str);
        }

        return $str;
    }
}
