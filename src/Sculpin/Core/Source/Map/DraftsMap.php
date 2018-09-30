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
        if ($source->data()->get('draft')) {
            $tags = $source->data()->get('tags');
            if (null === $tags) {
                $tags = ['drafts'];
            } else {
                if (!is_array($tags)) {
                    $tags = [];
                    if ($tags) {
                        $tags = [$tags];
                    }
                }

                if (! in_array('drafts', $tags)) {
                    // only add drafts if it isn't already in tags.
                    $tags[] = 'drafts';
                }
            }
            $source->data()->set('tags', $tags);
        }
    }
}
