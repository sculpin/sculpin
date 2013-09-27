<?php

namespace Sculpin\Contrib\ProxySourceCollection;

use Sculpin\Core\Source\SourceInterface;

class SimpleProxySourceItemFactory implements ProxySourceItemFactoryInterface
{
    private $reflectionClass;

    public function __construct($class = null)
    {
        $this->reflectionClass = new \ReflectionClass(
            $class ?: 'Sculpin\Contrib\ProxySourceCollection\ProxySourceItem'
        );
    }

    public function createProxySourceItem(SourceInterface $source)
    {
        return $this->reflectionClass->newInstance($source);
    }
}
