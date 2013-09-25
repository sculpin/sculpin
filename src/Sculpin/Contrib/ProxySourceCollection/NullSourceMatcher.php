<?php

namespace Sculpin\Contrib\ProxySourceCollection;

use Sculpin\Core\Source\SourceInterface;

class NullSourceMatcher implements SourceMatcherInterface
{
    public function matchSource(SourceInterface $source)
    {
        return false;
    }
}
