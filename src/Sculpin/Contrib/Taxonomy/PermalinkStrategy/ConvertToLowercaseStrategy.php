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

namespace Sculpin\Contrib\Taxonomy\PermalinkStrategy;

class ConvertToLowercaseStrategy implements PermalinkStrategyInterface
{
    public function process($str)
    {
        return strtolower($str);
    }
}
