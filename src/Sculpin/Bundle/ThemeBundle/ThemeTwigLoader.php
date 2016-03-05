<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\ThemeBundle;

use Sculpin\Bundle\TwigBundle\FlexibleExtensionFilesystemLoader;

class ThemeTwigLoader implements \Twig_LoaderInterface, \Twig_ExistsLoaderInterface
{
    /** @var \Twig_Loader_Chain */
    private $chainLoader;

    public function __construct(ThemeRegistry $themeRegistry, array $extensions)
    {
        $loaders = array();

        $theme = $themeRegistry->findActiveTheme();
        if (null !== $theme) {
            $paths = $this->findPaths($theme);
            if (isset($theme['parent'])) {
                $paths = $this->findPaths($theme['parent'], $paths);
            }

            if ($paths) {
                $loaders[] = new FlexibleExtensionFilesystemLoader('', array(), $paths, $extensions);
            }
        }

        $this->chainLoader = new \Twig_Loader_Chain($loaders);
    }

    private function findPaths($theme, array $paths = array())
    {
        foreach (array('_views', '_layouts', '_includes', '_partials') as $type) {
            if (is_dir($viewPath = $theme['path'].'/'.$type)) {
                $paths[] = $viewPath;
            }
        }

        return $paths;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource($name)
    {
        return $this->chainLoader->getSource($name);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return $this->chainLoader->exists($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey($name)
    {
        return $this->chainLoader->getCacheKey($name);
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($name, $time)
    {
        return $this->chainLoader->isFresh($name, $time);
    }
}
