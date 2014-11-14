<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Source;

/**
 * Composite Data Source.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class CompositeDataSource implements DataSourceInterface
{
    /**
     * Data sources
     *
     * @var array
     */
    private $dataSources = array();

    /**
     * Constructor.
     *
     * @param array $dataSources Data sources
     */
    public function __construct(array $dataSources = array())
    {
        foreach ($dataSources as $dataSource) {
            $this->dataSources[$dataSource->dataSourceId()] = $dataSource;
        }
    }

    /**
     * Add a Data Source.
     *
     * @param DataSourceInterface $dataSource Data Source
     */
    public function addDataSource(DataSourceInterface $dataSource)
    {
        $this->dataSources[$dataSource->dataSourceId()] = $dataSource;
    }

    /**
     * Backing Data Sources
     *
     * @return array
     */
    public function dataSources()
    {
        return $this->dataSources;
    }

    /**
     * {@inheritdoc}
     */
    public function dataSourceId()
    {
        return 'CompositeDataSource('.implode(',', array_map(function ($dataSource) {
            return $dataSource->dataSourceId();
        }, $this->dataSources));
    }

    /**
     * {@inheritdoc}
     */
    public function refresh(SourceSet $sourceSet)
    {
        foreach ($this->dataSources as $dataSource) {
            $dataSource->refresh($sourceSet);
        }
    }
}
