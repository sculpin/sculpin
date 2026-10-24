<?php

namespace Sculpin\Contrib\Taxonomy\Tests;

use PHPUnit\Framework\TestCase;
use Sculpin\Contrib\Taxonomy\PermalinkStrategy\ToyRocketStrategy;
use Sculpin\Contrib\Taxonomy\PermalinkStrategyCollection;

class PermalinkStrategyCollectionTest extends TestCase
{
    public function testPushAndProcess()
    {
        $mock = $this->createMock(ToyRocketStrategy::class);
        $mock->expects($this->once())->method('process');

        $collection = new PermalinkStrategyCollection();
        $collection->push($mock);

        $collection->process('test');
    }
}
