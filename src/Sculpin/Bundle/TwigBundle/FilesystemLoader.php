<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\TwigBundle;

/**
 * Filesystem Loader.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class FilesystemLoader extends \Twig_Loader_Filesystem
{
    /**
     * {@inheritdoc}
     */
    public function getCacheKey($name)
    {
        $filename = $this->findTemplate($name);

        return filemtime($filename).':'.$filename;
    }
}
