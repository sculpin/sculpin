<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\SculpinBundle\HttpKernel;

/**
 * Default Kernel implementation
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class DefaultKernel extends AbstractKernel
{
    /**
     * {@inheritdoc}
     */
    protected function getAdditionalSculpinBundles()
    {
        return array();
    }
}
