<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\ComposerBundle\Console;

/**
 * Composer aware Kernel interface.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
interface ComposerAwareApplicationInterface
{
    /**
     * Composer ClassLoader
     *
     * @return \Composer\Autoload\ClassLoader
     */
    public function getComposerClassLoader();

    /**
     * Internal vendor root
     *
     * @return string
     */
    public function getInternalVendorRoot();

    /**
     * Should the internally installed repository be enabled?
     *
     * @return bool
     */
    public function internallyInstalledRepositoryEnabled();

    /**
     * The Application's package
     *
     * @return \Composer\Package\PackageInterface
     */
    public function getApplicationPackage();
}
