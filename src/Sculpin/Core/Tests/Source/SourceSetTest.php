<?php

declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Tests\Source;

use PHPUnit\Framework\TestCase;
use Sculpin\Core\Source\SourceSet;
use Sculpin\Core\Source\SourceInterface;

class SourceSetTest extends TestCase
{
    public function makeTestSource($sourceId, $hasChanged = true)
    {
        $source = $this->createMock(SourceInterface::class);

        $source
            ->expects($this->any())
            ->method('sourceId')
            ->will($this->returnValue($sourceId));

        $source
            ->expects($this->any())
            ->method('hasChanged')
            ->will($this->returnValue($hasChanged));

        return $source;
    }

    public function testContainsSource()
    {
        $source000 = $this->makeTestSource('TestSource:000');
        $source001 = $this->makeTestSource('TestSource:001');
        $source002 = $this->makeTestSource('TestSource:002');

        $sourceSet = new SourceSet([$source000, $source002]);

        $this->assertTrue($sourceSet->containsSource($source000));
        $this->assertFalse($sourceSet->containsSource($source001));
        $this->assertTrue($sourceSet->containsSource($source002));

        // Ensure that the act of calling containsSource() does not
        // change the state.
        $this->assertTrue($sourceSet->containsSource($source000));
        $this->assertFalse($sourceSet->containsSource($source001));
        $this->assertTrue($sourceSet->containsSource($source002));
    }

    public function testMergeSource()
    {
        $source000a = $this->makeTestSource('TestSource:000');
        $source000a
            ->expects($this->any())
            ->method('content')
            ->will($this->returnValue('a'));

        $source000b = $this->makeTestSource('TestSource:000');
        $source000b
            ->expects($this->any())
            ->method('content')
            ->will($this->returnValue('b'));

        $sourceSet = new SourceSet;

        $this->assertFalse($sourceSet->containsSource($source000a));

        $sourceSet->mergeSource($source000a);

        $this->assertTrue($sourceSet->containsSource($source000a));

        $internalSources = $sourceSet->allSources();
        $this->assertEquals('a', $internalSources['TestSource:000']->content());

        $sourceSet->mergeSource($source000b);

        $internalSources = $sourceSet->allSources();
        $this->assertEquals('b', $internalSources['TestSource:000']->content());
    }

    public function testAllSources()
    {
        $source000 = $this->makeTestSource('TestSource:000');
        $source001 = $this->makeTestSource('TestSource:001');
        $source002 = $this->makeTestSource('TestSource:002');

        $sourceSet = new SourceSet([$source000, $source001, $source002]);

        $this->assertEquals([
            'TestSource:000' => $source000,
            'TestSource:001' => $source001,
            'TestSource:002' => $source002,
        ], $sourceSet->allSources());
    }

    public function testUpdatedSources()
    {
        $source000 = $this->makeTestSource('TestSource:000');
        $source001 = $this->makeTestSource('TestSource:001', false);
        $source002 = $this->makeTestSource('TestSource:002');

        $sourceSet = new SourceSet([$source000, $source001, $source002]);

        $this->assertEquals([
            'TestSource:000' => $source000,
            'TestSource:002' => $source002,
        ], $sourceSet->updatedSources());
    }

    public function testReset()
    {
        $source000 = $this->makeTestSource('TestSource:000');
        $source001 = $this->makeTestSource('TestSource:001');
        $source002 = $this->makeTestSource('TestSource:002');

        foreach ([$source000, $source001, $source002] as $source) {
            $source
                ->expects($this->once())
                ->method('setHasNotChanged');
        }

        $sourceSet = new SourceSet([$source000, $source001, $source002]);
        $sourceSet->reset();
    }
}
