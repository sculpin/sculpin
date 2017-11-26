<?php declare(strict_types=1);

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
    private $excludes = [];

    /**
     * Ignore patterns
     *
     * @var array
     */
    private $ignores = [];

    /**
     * Raw patterns
     *
     * @var array
     */
    private $raws = [];

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
    public function setExcludes(array $excludes = []): Configuration
    {
        foreach ($excludes as $exclude) {
            $this->addExclude($exclude);
        }

        return $this;
    }

    /**
     * Add an exclude pattern
     *
     *
     * @return Configuration
     */
    public function addExclude(string $pattern): Configuration
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
     */
    public function excludes(): array
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
    public function setIgnores(array $ignores = []): Configuration
    {
        foreach ($ignores as $ignore) {
            $this->addIgnore($ignore);
        }

        return $this;
    }

    /**
     * Add an ignore pattern
     *
     *
     * @return Configuration
     */
    public function addIgnore(string $pattern): Configuration
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
     */
    public function ignores(): array
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
    public function setRaws(array $raws = []): Configuration
    {
        foreach ($raws as $raw) {
            $this->addRaw($raw);
        }

        return $this;
    }

    /**
     * Add a raw pattern
     *
     *
     * @return Configuration
     */
    public function addRaw(string $pattern): Configuration
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
     */
    public function raws(): array
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
    public function setSourceDir(string $sourceDir): Configuration
    {
        $this->sourceDir = $sourceDir;

        return $this;
    }

    /**
     * Source directory
     */
    public function sourceDir(): string
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
    public function setOutputDir(string $outputDir)
    {
        $this->outputDir = $outputDir;

        return $this;
    }

    /**
     * Output directory
     */
    public function outputDir(): string
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
    public function setPermalink(string $permalink)
    {
        $this->permalink = $permalink;

        return $this;
    }

    /**
     * Permalink
     */
    public function permalink(): string
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
    public function setDefaultFormatter(string $defaultFormatter): Configuration
    {
        $this->defaultFormatter = $defaultFormatter;

        return $this;
    }

    /**
     * Default formatter
     */
    public function defaultFormatter(): string
    {
        return $this->defaultFormatter;
    }
}
