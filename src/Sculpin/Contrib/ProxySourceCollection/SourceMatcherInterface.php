<?php

namespace Sculpin\Contrib\ProxySourceCollection;

use Sculpin\Core\Source\SourceInterface;

interface SourceMatcherInterface
{
    public function matchSource(SourceInterface $source);
}
