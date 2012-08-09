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
    private $dataSources;

    /**
     * Constructor.
     *
     * @param array $dataSources Data sources
     */
    public function __construct(array $dataSources = array())
    {
        $this->dataSources = $dataSources;
    }

    /**
     * Add a Data Source.
     *
     * @param DataSourceInterface $dataSource Data Source
     */
    public function addDataSource(DataSourceInterface $dataSource)
    {
        $this->dataSources[] = $dataSource;
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
