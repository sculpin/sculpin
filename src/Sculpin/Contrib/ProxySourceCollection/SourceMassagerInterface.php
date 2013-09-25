<?php

namespace Sculpin\Contrib\ProxySourceCollection;

use Sculpin\Core\Source\SourceInterface;

interface SourceMassagerInterface
{
    public function massageSource(SourceInterface $source);
}
