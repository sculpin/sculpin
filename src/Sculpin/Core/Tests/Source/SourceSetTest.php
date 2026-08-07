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

use Dflydev\DotAccessConfiguration\ConfigurationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sculpin\Core\Source\SourceSet;
use Sculpin\Core\Source\SourceInterface;

final class SourceSetTest extends TestCase
{
    public function makeTestSource($sourceId, $hasChanged = true, array $dataMap = []): MockObject&SourceInterface
    {
        $source = $this->createMock(SourceInterface::class);
        $data = $this->createMock(ConfigurationInterface::class);

        if ($dataMap) {
            $data
                ->expects($this->any())
                ->method('get')
                ->willReturnCallback(function ($key) use ($dataMap) {
                    return $dataMap[$key] ?? null;
                });
        }

        $source
            ->expects($this->any())
            ->method('sourceId')
            ->will($this->returnValue($sourceId));

        $source
            ->expects($this->any())
            ->method('hasChanged')
            ->will($this->returnValue($hasChanged));

        $source
            ->expects($this->any())
            ->method('data')
            ->willReturn($data);

        return $source;
    }

    public function testContainsSource(): void
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

    public function testMergeSource(): void
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

    public function testAllSources(): void
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

    public function testUpdatedSources(): void
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

    public function testReset(): void
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

    public function testSort(): void
    {
        $source000 = $this->makeTestSource(
            'TestSource:000',
            dataMap: ['use' => ['jacksons']]
        );
        $source001 = $this->makeTestSource('TestSource:001');
        $source002 = $this->makeTestSource('TestSource:002');

        $source000->expects($this->any())->method('content')->will($this->returnValue('a'));
        $source001->expects($this->any())->method('content')->will($this->returnValue('b'));
        $source002->expects($this->any())->method('content')->will($this->returnValue('c'));
        $source000->expects($this->exactly(2))->method('isRaw')->willReturn(false);

        $sourceSet = new SourceSet([
            $source000,
            $source001,
            $source002,
        ]);

        $this->assertSame(
            ['TestSource:000', 'TestSource:001', 'TestSource:002'],
            $sourceSet->allSources()|>array_keys(...)
        );

        $sourceSet->sort();

        $this->assertSame(
            ['TestSource:001', 'TestSource:002', 'TestSource:000'],
            $sourceSet->allSources()|>array_keys(...),
            'Item with "use" data provider is sorted last'
        );
    }
}
