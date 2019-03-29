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

final class PermalinkStrategyCollectionFactory
{
    /**
     * @param string|array $taxonomy
     */
    public static function create($taxonomy): PermalinkStrategyCollection
    {
        $collection = new PermalinkStrategyCollection();
        if (is_string($taxonomy)) {
            return $collection;
        }
        foreach ($taxonomy['strategies'] ?? [] as $strategy) {
            $collection->push(new $strategy());
        }

        return $collection;
    }
}
