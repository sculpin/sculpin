<?php

/*
 * This file is a part of Sculpin.
 * 
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\bundle\twigBundle;

class FilesystemTwigLoader extends \Twig_Loader_Filesystem
{
    /**
     * (non-PHPdoc)
     * @see Twig_Loader_Filesystem::getCacheKey()
     */
    public function getCacheKey($name)
    {
        $filename = $this->findTemplate($name);
        return filemtime($filename).':'.$filename;
    }
}
