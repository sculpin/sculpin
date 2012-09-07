<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\SculpinBundle\Composer;

use Composer\Package\Package;
use Sculpin\Core\Sculpin;

/**
 * Composer Package Factory for Sculpin
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class PackageFactory
{
    /**
     * Create
     *
     * @return \Composer\Package\PackageInterface
     */
    public static function create()
    {
        // If we have an actual packag eversion set we should use that
        // otherwise we should use a sensible default value.
        $version = ('@'.'package_version'.'@' !== Sculpin::VERSION) ? Sculpin::VERSION : '2.0.x-dev';

        return new Package('sculpin/sculpin', $version, $version);
    }
}
