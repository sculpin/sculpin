<?php

namespace Sculpin\Core\Source\Map;

use Sculpin\Core\Source\SourceInterface;

class DefaultPermalinkMap implements MapInterface
{
    private $defaultPermalink;

    public function __construct($defaultPermalink = null)
    {
        $this->defaultPermalink = $defaultPermalink;
    }

    public function process(SourceInterface $source)
    {
        if (!$source->data()->get('permalink') and $this->defaultPermalink) {
            $source->data()->set('permalink', $this->defaultPermalink);
        }
    }
}
