<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Tests\Source;

use Sculpin\Core\Source\CompositeDataSource;
use Sculpin\Core\Source\SourceSet;

class CompositeDataSourceTest extends \PHPUnit_Framework_TestCase
{
    public function makeDataSource($dataSourceId)
    {
        $dataSource = $this->getMock('Sculpin\Core\Source\DataSourceInterface');

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

        $dataSource = new CompositeDataSource(array($ds000, $ds002));

        $this->assertEquals(array(
            'TestDataSource:000' => $ds000,
            'TestDataSource:002' => $ds002,
        ), $dataSource->dataSources());

        $dataSource->addDataSource($ds001);

        $this->assertEquals(array(
            'TestDataSource:000' => $ds000,
            'TestDataSource:002' => $ds002,
            'TestDataSource:001' => $ds001,
        ), $dataSource->dataSources());
    }

    public function testDataSourceId()
    {
        $ds000 = $this->makeDataSource('TestDataSource:000');
        $ds001 = $this->makeDataSource('TestDataSource:001');
        $ds002 = $this->makeDataSource('TestDataSource:002');

        $dataSource = new CompositeDataSource(array($ds000, $ds001, $ds002));

        $this->assertContains('TestDataSource:000', $dataSource->dataSourceId());
        $this->assertContains('TestDataSource:001', $dataSource->dataSourceId());
        $this->assertContains('TestDataSource:002', $dataSource->dataSourceId());
    }

    public function testRefresh()
    {
        $sourceSet = new SourceSet;

        $ds000 = $this->makeDataSource('TestDataSource:000');
        $ds001 = $this->makeDataSource('TestDataSource:001');
        $ds002 = $this->makeDataSource('TestDataSource:002');

        foreach (array($ds000, $ds001, $ds002) as $dataSource) {
            $dataSource
                ->expects($this->once())
                ->method('refresh')
                ->with($this->equalTo($sourceSet));
        }

        $dataSource = new CompositeDataSource(array($ds000, $ds001, $ds002));

        $dataSource->refresh($sourceSet);
    }
}
