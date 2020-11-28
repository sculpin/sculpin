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
use Sculpin\Core\Source\CompositeDataSource;
use Sculpin\Core\Source\SourceSet;
use Sculpin\Core\Source\DataSourceInterface;

class CompositeDataSourceTest extends TestCase
{
    public function makeDataSource($dataSourceId)
    {
        $dataSource = $this->createMock(DataSourceInterface::class);

        $dataSource
            ->expects($this->any())
            ->method('dataSourceId')
            ->will($this->returnValue($dataSourceId));

        return $dataSource;
    }

    public function testAddDataSource()
    {
        $ds000 = $this->makeDataSource('TestDataSource:000');
        $ds001 = $this->makeDataSource('TestDataSource:001');
        $ds002 = $this->makeDataSource('TestDataSource:002');

        $dataSource = new CompositeDataSource([$ds000, $ds002]);

        $this->assertEquals([
            'TestDataSource:000' => $ds000,
            'TestDataSource:002' => $ds002,
        ], $dataSource->dataSources());

        $dataSource->addDataSource($ds001);

        $this->assertEquals([
            'TestDataSource:000' => $ds000,
            'TestDataSource:002' => $ds002,
            'TestDataSource:001' => $ds001,
        ], $dataSource->dataSources());
    }

    public function testDataSourceId()
    {
        $ds000 = $this->makeDataSource('TestDataSource:000');
        $ds001 = $this->makeDataSource('TestDataSource:001');
        $ds002 = $this->makeDataSource('TestDataSource:002');

        $dataSource = new CompositeDataSource([$ds000, $ds001, $ds002]);

        $this->assertStringContainsString('TestDataSource:000', $dataSource->dataSourceId());
        $this->assertStringContainsString('TestDataSource:001', $dataSource->dataSourceId());
        $this->assertStringContainsString('TestDataSource:002', $dataSource->dataSourceId());
    }

    public function testRefresh()
    {
        $sourceSet = new SourceSet;

        $ds000 = $this->makeDataSource('TestDataSource:000');
        $ds001 = $this->makeDataSource('TestDataSource:001');
        $ds002 = $this->makeDataSource('TestDataSource:002');

        foreach ([$ds000, $ds001, $ds002] as $dataSource) {
            $dataSource
                ->expects($this->once())
                ->method('refresh')
                ->with($this->equalTo($sourceSet));
        }

        $dataSource = new CompositeDataSource([$ds000, $ds001, $ds002]);

        $dataSource->refresh($sourceSet);
    }
}
