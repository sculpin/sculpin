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
 * Flexible Extension Filesystem Loader.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class FlexibleExtensionFilesystemLoader implements \Twig_LoaderInterface
{
    /**
     * Filesystem loader
     *
     * @var FilesystemLoader
     */
    protected $filesystemLoader;

    /**
     * Constructor.
     *
     * @param array  $paths      Paths
     * @param array  $extensions Extensions
     */
    public function __construct(array $paths, array $extensions)
    {
        $this->filesystemLoader = new FilesystemLoader(array_filter($paths, function($path) {
            return file_exists($path);
        }));
        $this->extensions = array_map(function($ext) {
            return $ext?'.'.$ext:$ext;
        }, $extensions);
    }

    /**
     * Gets the source code of a template, given its name.
     *
     * @param string $name The name of the template to load
     *
     * @return string The template source code
     */
    public function getSource($name)
    {
        foreach ($this->extensions as $extension) {
            try {
                return $this->filesystemLoader->getSource($name.$extension);
            } catch (\Twig_Error_Loader $e) {
            }
        }
        throw new \Twig_Error_Loader(sprintf('Template "%s" is not defined.', $name));
    }

    /**
     * Gets the cache key to use for the cache for a given template name.
     *
     * @param string $name The name of the template to load
     *
     * @return string The cache key
     */
    public function getCacheKey($name)
    {
        foreach ($this->extensions as $extension) {
            try {
                return $this->filesystemLoader->getCacheKey($name.$extension);
            } catch (\Twig_Error_Loader $e) {
            }
        }
        throw new \Twig_Error_Loader(sprintf('Template "%s" is not defined.', $name));
    }

    /**
     * Returns true if the template is still fresh.
     *
     * @param string    $name The template name
     * @param timestamp $time The last modification time of the cached template
     *
     * @return bool
     */
    public function isFresh($name, $time)
    {
        foreach ($this->extensions as $extension) {
            try {
                return $this->filesystemLoader->isFresh($name.$extension, $time);
            } catch (\Twig_Error_Loader $e) {
            }
        }
        throw new \Twig_Error_Loader(sprintf('Template "%s" is not defined.', $name));
    }
}
