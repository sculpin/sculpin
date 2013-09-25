<?php

namespace Sculpin\Contrib\ProxySourceCollection;

use Sculpin\Core\Source\SourceInterface;

interface ProxySourceItemFactoryInterface
{
    public function createProxySourceItem(SourceInterface $source);
}
