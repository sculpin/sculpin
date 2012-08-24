<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\DataProvider;

/**
 * Data Provider Interface.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
interface DataProviderInterface
{
    /**
     * Provide data.
     *
     * @return array
     */
    public function provideData();
}
