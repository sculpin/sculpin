<?php

declare(strict_types=1);

namespace Sculpin\Bundle\ThemeBundle;

class ThemeTwigExtension extends \Twig_Extension
{
    private $theme;
    private $sourceDir;
    private $directory;

    public function __construct(ThemeRegistry $themeRegistry, $sourceDir, $directory)
    {
        $this->theme = $themeRegistry->findActiveTheme();
        $this->sourceDir = $sourceDir;
        $this->directory = $directory;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction('theme_path', [$this, 'themePath']),
            new \Twig_SimpleFunction('theme_path_exists', [$this, 'themePathExists']),
            new \Twig_SimpleFunction('theme_paths', [$this, 'themePaths']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'theme';
    }

    /**
     * Generate a URL for a Theme's resource
     *
     * Will always return a value. Default return value is the input unless the
     * file actually exists at a theme location.
     *
     * @param string $resource
     *
     * @return string
     */
    public function themePath(string $resource): string
    {
        if (null === $this->theme) {
            return $resource;
        }

        if (file_exists($this->sourceDir.'/'.$resource)) {
            return $resource;
        }

        $themeResource = $this->findThemeResource($this->theme, $resource);
        if (null !== $themeResource) {
            return $themeResource;
        }

        if (isset($this->theme['parent'])) {
            $themeResource = $this->findThemeResource($this->theme['parent'], $resource);
            if (null !== $themeResource) {
                return $themeResource;
            }
        }

        return $resource;
    }

    /**
     * Check to see if a given Theme resource exists anywhere on disk
     *
     * @param string $resource
     *
     * @return bool
     */
    public function themePathExists(string $resource): bool
    {
        if (file_exists($this->sourceDir.'/'.$resource)) {
            return true;
        }

        if (null === $this->theme) {
            return false;
        }

        $themeResource = $this->findThemeResource($this->theme, $resource);
        if (null !== $themeResource) {
            return true;
        }

        if (isset($this->theme['parent'])) {
            $themeResource = $this->findThemeResource($this->theme['parent'], $resource);
            if (null !== $themeResource) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a collection of URLs for a Theme's resource
     *
     * May end up returning an empty array.
     *
     * @param string $resource
     *
     * @return array
     */
    public function themePaths(string $resource): array
    {
        $paths = [];

        if (file_exists($this->sourceDir.'/'.$resource)) {
            $paths[] = $resource;
        }

        if (null === $this->theme) {
            return $paths;
        }

        $themeResource = $this->findThemeResource($this->theme, $resource);
        if (null !== $themeResource) {
            $paths[] = $themeResource;
        }

        if (isset($this->theme['parent'])) {
            $themeResource = $this->findThemeResource($this->theme['parent'], $resource);
            if (null !== $themeResource) {
                $paths[] = $themeResource;
            }
        }

        return array_reverse($paths);
    }

    private function findThemeResource(array $theme, string $resource): string
    {
        if (file_exists($theme['path'].'/'.$resource)) {
            return $this->directory.'/'.$theme['name'].'/'.$resource;
        }
    }
}
