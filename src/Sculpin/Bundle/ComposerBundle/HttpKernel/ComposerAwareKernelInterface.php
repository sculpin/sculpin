<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\ComposerBundle\HttpKernel;

/**
 * Composer aware Kernel interface.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
interface ComposerAwareKernelInterface
{
    /**
     * Set internal vendor root
     *
     * @param string $internalVendorRoot Internal vendor root
     */
    public function setInternalVendorRoot($internalVendorRoot);

    /**
     * Internal vendor root
     *
     * @return string
     */
    public function getInternalVendorRoot();
}
