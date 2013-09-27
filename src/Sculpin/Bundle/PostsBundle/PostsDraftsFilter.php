<?php

namespace Sculpin\Bundle\PostsBundle;

use Sculpin\Core\Source\Filter\FilterInterface;
use Sculpin\Core\Source\SourceInterface;
use Sculpin\Core\Util\DirectorySeparatorNormalizer;

class PostsDraftsFilter implements FilterInterface
{
    private $publishDrafts;

    public function __construct($publishDrafts = false) {
        $this->publishDrafts = $publishDrafts;
    }

    public function match(SourceInterface $source)
    {
        if ($source->data()->get('draft')) {
            if (!$this->publishDrafts) {
                // If we are not configured to publish drafts we should
                // inform the source that it should be skipped. This
                // will ensure that it won't be touched by any other
                // part of the system for this run.
                $source->setShouldBeSkipped();

                return false;
            }
        }

        return true;
    }
}
