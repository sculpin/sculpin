<?php

namespace Sculpin\Contrib\ProxySourceCollection;

use Sculpin\Core\Source\SourceInterface;

class NullSourceMassager implements SourceMassagerInterface
{
    public function massageSource(SourceInterface $source)
    {
        // NOOP
    }
}
