<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Tests\Formatter;

use Sculpin\Core\Source\CompositeDataSource;
use Sculpin\Core\Source\SourceSet;
use Sculpin\Core\Source\DataSourceInterface;
use Sculpin\Core\Tests\Base;
use \PHPUnit_Framework_MockObject_MockObject;

class CompositeDataSourceTest extends Base
{
    /**
     * @param string $dataSourceId
     * @return PHPUnit_Framework_MockObject_MockObject|DataSourceInterface
     */
    public function makeDataSource($dataSourceId)
    {
        $dataSource = $this
            ->getMockBuilder('Sculpin\Core\Source\DataSourceInterface')
            ->getMock();

        $dataSource
            ->expects($this->any())
            ->method('dataSourceId')
            ->will($this->returnValue($dataSourceId));

        return $dataSource;
    }

    public function testAddDataSourceSetsDataSourcesAsArray()
    {
        $ds000 = $this->makeDataSource('TestDataSource:000');
        $ds001 = $this->makeDataSource('TestDataSource:001');
        $ds002 = $this->makeDataSource('TestDataSource:002');

        $dataSource = new CompositeDataSource(array($ds000, $ds002));

        $this->assertEquals(
            array(
                'TestDataSource:000' => $ds000,
                'TestDataSource:002' => $ds002,
            ),
            $dataSource->dataSources()
        );

        $dataSource->addDataSource($ds001);

        $this->assertEquals(
            array(
                'TestDataSource:000' => $ds000,
                'TestDataSource:002' => $ds002,
                'TestDataSource:001' => $ds001,
            ),
            $dataSource->dataSources()
        );
    }

    public function testCompositeDataSourceSetsDataSourceId()
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
