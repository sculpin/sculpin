<?php

namespace Sculpin\Bundle\PostsBundle;

use Sculpin\Core\Source\Map\MapInterface;
use Sculpin\Core\Source\SourceInterface;

class PostsDraftsMap implements MapInterface
{
    public function process(SourceInterface $source)
    {
        if ($source->data()->get('draft')) {
            $tags = $source->data()->get('tags');
            if (null === $tags) {
                $tags = array('drafts');
            } else {
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
            }
            $source->data()->set('tags', $tags);
        }
    }
}
