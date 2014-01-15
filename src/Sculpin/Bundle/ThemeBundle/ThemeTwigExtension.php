<?php

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
    public function getFunctions()
    {
        return array(
            'theme_path' => new \Twig_Function_Method($this, 'generateThemePath'),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'theme';
    }

    /**
     * Generate a URL for a Theme's resource
     *
     * @param string $resource
     * @param boolean $skipLocalFiles
     *
     * @return string
     */
    public function generateThemePath($resource, $skipLocalFiles = false)
    {
        if (null === $this->theme) {
            return $resource;
        }

        if (! $skipLocalFiles) {
            if (file_exists($this->sourceDir.'/'.$resource)) {
                return $resource;
            }
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

    private function findThemeResource($theme, $resource)
    {
        if (file_exists($theme['path'].'/'.$resource)) {
            return $this->directory.'/'.$theme['name'].'/'.$resource;
        }
    }
}
