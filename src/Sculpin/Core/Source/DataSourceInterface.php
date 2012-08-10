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
 * Data Source Interface.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
interface DataSourceInterface
{
    /**
     * Data Source ID
     *
     * @return string
     */
    public function dataSourceId();

    /**
     * Refresh the Source Set with updated Sources.
     *
     * @param SourceSet $sourceSet Source set to be updated
     */
    public function refresh(SourceSet $sourceSet);
}
