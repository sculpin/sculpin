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

namespace Sculpin\Core\Source\Map;

use Sculpin\Core\Source\SourceInterface;

class DraftsMap implements MapInterface
{
    public function process(SourceInterface $source): void
    {
        if (!$source->data()->get('draft')) {
            return;
        }

        $tags = $source->data()->get('tags') ?? [];

        // Convert string-only tag into a single-element array
        if (is_string($tags)) {
            $tags = [$tags];
        }

        if (!is_array($tags)) {
            $tags = [];
        }

        // Only add drafts if not already present in $tags
        if (!\in_array('drafts', $tags)) {
            $tags[] = 'drafts';
        }

        $source->data()->set('tags', $tags);
    }
}
