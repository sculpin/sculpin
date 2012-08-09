<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Finder;

use Symfony\Component\Finder\Finder;

/**
 * Finder factory.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class FinderFactory
{
    /**
     * Create a Finder.
     *
     * @return Finder
     */
    public function create()
    {
        return new Finder;
    }
}
