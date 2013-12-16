<?php

namespace Sculpin\Bundle\ThemeBundle;

use Sculpin\Bundle\TwigBundle\FlexibleExtensionFilesystemLoader;

class ThemeTwigLoader implements \Twig_LoaderInterface, \Twig_ExistsLoaderInterface
{
    private $chainLoader;

    public function __construct(ThemeRegistry $themeRegistry, array $extensions)
    {
        $loaders = array();

        $theme = $themeRegistry->findActiveTheme();
        if (null !== $theme) {
            if (isset($theme['_views'])) {
                $loaders[] = new FlexibleExtensionFilesystemLoader('', array(), array($theme['_views']), $extensions);
            }

            if (isset($theme['parent']) && isset($theme['parent']['_views'])) {
                $loaders[] = new FlexibleExtensionFilesystemLoader('', array(), array($theme['parent']['_views']), $extensions);
            }
        }

        $this->chainLoader = new \Twig_Loader_Chain($loaders);
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
