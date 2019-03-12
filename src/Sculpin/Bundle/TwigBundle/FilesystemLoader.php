<?php

declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\TwigBundle;

use Twig\Loader\FilesystemLoader as TwigFilesystemLoader;

/**
 * I twig loader that uses the file modification timestamp in the cache key.
 *
 * @method getSourceContext(string $name) \Twig\Source
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class FilesystemLoader extends TwigFilesystemLoader
{
    /**
     * {@inheritdoc}
     */
    public function getCacheKey($name): string
    {
        $filename = $this->findTemplate($name);

        return filemtime($filename).':'.$filename;
    }
}
