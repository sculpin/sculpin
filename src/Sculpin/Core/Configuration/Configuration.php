<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Configuration;

use Dflydev\DotAccessConfiguration\Configuration as BaseConfiguration;

/**
 * Configuration.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class Configuration extends BaseConfiguration
{
    /**
     * Exclusion patterns
     *
     * @var array
     */
    private $excludes = array();

    /**
     * Ignore patterns
     *
     * @var array
     */
    private $ignores = array();

    /**
     * Raw patterns
     *
     * @var array
     */
    private $raws = array();

    /**
     * Source directory
     *
     * @var string
     */
    private $sourceDir;

    /**
     * Output directory
     *
     * @var string
     */
    private $outputDir;

    /**
     * Default permalink
     *
     * @var string
     */
    private $permalink;

    /**
     * Default formatter
     *
     * @var string
     */
    private $defaultFormatter;

    /**
     * Set excludes
     *
     * NOTE: Does not clear existing values first.
     *
     * @param array $excludes Excludes.
     *
     * @return Configuration
     */
    public function setExcludes(array $excludes = array())
    {
        foreach ($excludes as $exclude) {
            $this->addExclude($exclude);
        }

        return $this;
    }

    /**
     * Add an exclude pattern
     *
     * @param string $pattern
     *
     * @return Configuration
     */
    public function addExclude($pattern)
    {
        if (substr($pattern, 0, 2)=='./') {
            $pattern = substr($pattern, 2);
        }

        if (!in_array($pattern, $this->excludes)) {
            $this->excludes[] = $pattern;
        }

        return $this;
    }

    /**
     * Excludes
     *
     * @return array
     */
    public function excludes()
    {
        return $this->excludes;
    }

    /**
     * Set ignores
     *
     * NOTE: Does not clear existing values first.
     *
     * @param array $ignores Ignores.
     *
     * @return Configuration
     */
    public function setIgnores(array $ignores = array())
    {
        foreach ($ignores as $ignore) {
            $this->addIgnore($ignore);
        }

        return $this;
    }

    /**
     * Add an ignore pattern
     *
     * @param string $pattern
     *
     * @return Configuration
     */
    public function addIgnore($pattern)
    {
        if (substr($pattern, 0, 2)=='./') {
            $pattern = substr($pattern, 2);
        }

        if (!in_array($pattern, $this->ignores)) {
            $this->ignores[] = $pattern;
        }

        return $this;
    }

    /**
     * Ignores
     *
     * @return array
     */
    public function ignores()
    {
        return $this->ignores;
    }

    /**
     * Set raws
     *
     * NOTE: Does not clear existing values first.
     *
     * @param array $raws Raws.
     *
     * @return Configuration
     */
    public function setRaws(array $raws = array())
    {
        foreach ($raws as $raw) {
            $this->addRaw($raw);
        }

        return $this;
    }

    /**
     * Add a raw pattern
     *
     * @param string $pattern
     *
     * @return Configuration
     */
    public function addRaw($pattern)
    {
        if (substr($pattern, 0, 2)=='./') {
            $pattern = substr($pattern, 2);
        }
        if (!in_array($pattern, $this->raws)) {
            $this->raws[] = $pattern;
        }

        return $this;
    }

    /**
     * Raws
     *
     * @return array
     */
    public function raws()
    {
        return $this->raws;
    }

    /**
     * Set source directory
     *
     * @param string $sourceDir Source directory
     *
     * @return Configuration
     */
    public function setSourceDir($sourceDir)
    {
        $this->sourceDir = $sourceDir;

        return $this;
    }

    /**
     * Source directory
     *
     * @return string
     */
    public function sourceDir()
    {
        return $this->sourceDir;
    }

    /**
     * Set output directory
     *
     * @param string $outputDir Output directory
     *
     * @return $this
     */
    public function setOutputDir($outputDir)
    {
        $this->outputDir = $outputDir;

        return $this;
    }

    /**
     * Output directory
     *
     * @return string
     */
    public function outputDir()
    {
        return $this->outputDir;
    }

    /**
     * Set permalink
     *
     * @param string $permalink Permalink
     *
     * @return $this
     */
    public function setPermalink($permalink)
    {
        $this->permalink = $permalink;

        return $this;
    }

    /**
     * Permalink
     *
     * @return string
     */
    public function permalink()
    {
        return $this->permalink;
    }

    /**
     * Set default formatter
     *
     * @param string $defaultFormatter Default formatter
     *
     * @return Configuration
     */
    public function setDefaultFormatter($defaultFormatter)
    {
        $this->defaultFormatter = $defaultFormatter;

        return $this;
    }

    /**
     * Default formatter
     *
     * @return string
     */
    public function defaultFormatter()
    {
        return $this->defaultFormatter;
    }
}
