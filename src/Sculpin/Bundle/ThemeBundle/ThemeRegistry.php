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

use Dflydev\Symfony\FinderFactory\FinderFactoryInterface;
use Symfony\Component\Yaml\Yaml;

class ThemeRegistry
{
    private $finderFactory;
    private $directory;
    private $activeTheme;

    public function __construct(FinderFactoryInterface $finderFactory, $directory, $activeTheme = null)
    {
        $this->finderFactory = $finderFactory;
        $this->directory = $directory;
        $this->activeTheme = $activeTheme;
    }

    public function listThemes()
    {
        if (! file_exists($this->directory)) {
            return array();
        }

        $directories = $this
            ->finderFactory->createFinder()
            ->directories()
            ->ignoreVCS(true)
            ->depth('== 1')
            ->in($this->directory);

        $themes = array();

        foreach ($directories as $directory) {
            $name = basename(dirname($directory)).'/'.basename($directory);
            $theme = array('name' => $name, 'path' => $directory);
            if (file_exists($directory.'/theme.yml')) {
                $theme = array_merge(Yaml::parse(file_get_contents($directory.'/theme.yml')), $theme);
            }
            $themes[$theme['name']] = $theme;
        }

        return $themes;
    }

    public function findActiveTheme()
    {
        $themes = $this->listThemes();

        foreach (array($this->activeTheme.'-dev', $this->activeTheme) as $activeTheme) {
            if (! isset($themes[$activeTheme])) {
                continue;
            }

            $theme = $themes[$activeTheme];
            if (isset($theme['parent'])) {
                if (! isset($themes[$theme['parent']])) {
                    throw new \RuntimeException(sprintf(
                        "Theme %s is a child of nonexistent parent theme %s",
                        $this->activeTheme,
                        $theme['parent']
                    ));
                }

                $theme['parent'] = $themes[$theme['parent']];
            }

            return $theme;
        }
    }
}
