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

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class ThemeRegistry
{
    private $finderFactory;
    private $directory;
    private $activeTheme;

    public function __construct($finderFactory, string $directory, ?string $activeTheme = null)
    {
        $this->finderFactory = $finderFactory;
        $this->directory = $directory;
        $this->activeTheme = $activeTheme;
    }

    public function listThemes(): array
    {
        if (! file_exists($this->directory)) {
            return [];
        }

        $directories = Finder::create()
            ->directories()
            ->ignoreVCS(true)
            ->depth('== 1')
            ->in($this->directory);

        $themes = [];

        /** @var \SplFileInfo $directory */
        foreach ($directories as $directory) {
            $name = basename(dirname($directory->getRealPath())).'/'.basename($directory->getRealPath());
            $theme = ['name' => $name, 'path' => $directory];
            if (file_exists($directory.'/theme.yml')) {
                $theme = array_merge((array) Yaml::parse(file_get_contents($directory.'/theme.yml')), $theme);
            }
            $themes[$theme['name']] = $theme;
        }

        return $themes;
    }

    public function findActiveTheme(): ?array
    {
        $themes = $this->listThemes();

        foreach ([$this->activeTheme.'-dev', $this->activeTheme] as $activeTheme) {
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

        return null;
    }
}
