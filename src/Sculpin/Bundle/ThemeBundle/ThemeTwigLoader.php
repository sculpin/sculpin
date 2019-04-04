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

namespace Sculpin\Bundle\ThemeBundle;

use Sculpin\Bundle\TwigBundle\FlexibleExtensionFilesystemLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\LoaderInterface;

class ThemeTwigLoader implements LoaderInterface
{
    /**
     * @var ChainLoader
     */
    private $chainLoader;

    public function __construct(ThemeRegistry $themeRegistry, array $extensions)
    {
        $loaders = [];

        $theme = $themeRegistry->findActiveTheme();
        if (null !== $theme) {
            $paths = $this->findPaths($theme);
            if (isset($theme['parent'])) {
                $paths = $this->findPaths($theme['parent'], $paths);
            }

            if ($paths) {
                $loaders[] = new FlexibleExtensionFilesystemLoader('', [], $paths, $extensions);
            }
        }

        $this->chainLoader = new ChainLoader($loaders);
    }

    private function findPaths(array $theme, array $paths = []): array
    {
        foreach (['_views', '_layouts', '_includes', '_partials'] as $type) {
            if (is_dir($viewPath = $theme['path'].'/'.$type)) {
                $paths[] = $viewPath;
            }
        }

        return $paths;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceContext($name)
    {
        return $this->chainLoader->getSourceContext($name);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name): bool
    {
        return $this->chainLoader->exists($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey($name): string
    {
        return $this->chainLoader->getCacheKey($name);
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($name, $time): bool
    {
        return $this->chainLoader->isFresh($name, $time);
    }
}
