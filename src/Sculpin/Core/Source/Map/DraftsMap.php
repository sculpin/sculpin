<?php

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
    // FIXME multiple re-assignment of tags makes method 
    // difficult to reduce maintanance complexity
    public function process(SourceInterface $source)
    {
        if ($source->data()->get('draft')) {
            return;
        }

        $tags = $source->data()->get('tags');
        if (null === $tags) {
            $tags = array('drafts');
            $source->data()->set('tags', $tags);

            return;
        }

        if (!is_array($tags)) {
            if ($tags) {
                $tags = array($tags);
            } else {
                $tags = array();
            }
        }

        if (! in_array('drafts', $tags)) {
            // only add drafts if it isn't already in tags.
            $tags[] = 'drafts';
        }

        $source->data()->set('tags', $tags);
    }
}
