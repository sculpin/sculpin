<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\EmbeddedComposerBundle;

/**
 * Composer aware Kernel interface.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
interface EmbeddedComposerAwareInterface
{
    /**
     * Embedded Composer.
     *
     * @return EmbeddedComposer
     */
    public function getEmbeddedComposer();
}
