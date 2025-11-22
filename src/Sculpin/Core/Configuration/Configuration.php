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

namespace Sculpin\Core\Configuration;

use Dflydev\DotAccessConfiguration\Configuration as BaseConfiguration;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
class Configuration extends BaseConfiguration
{
    /**
     * Exclusion patterns
     */
    private array $excludes = [];

    /**
     * Ignore patterns
     */
    private array $ignores = [];

    /**
     * Raw patterns
     */
    private array $raws = [];

    /**
     * Source directory
     */
    private string $sourceDir;

    /**
     * Output directory
     */
    private string $outputDir;

    /**
     * Default permalink
     */
    private string $permalink;

    /**
     * Default formatter
     */
    private string $defaultFormatter;

    /**
     * Set excludes
     *
     * NOTE: Does not clear existing values first.
     *
     * @param array $excludes Excludes.
     */
    public function setExcludes(array $excludes = []): self
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
     */
    public function addExclude(string $pattern): self
    {
        if (str_starts_with($pattern, './')) {
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
     */
    public function setIgnores(array $ignores = []): self
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
     */
    public function addIgnore(string $pattern): self
    {
        if (str_starts_with($pattern, './')) {
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
     */
    public function setRaws(array $raws = []): self
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
     */
    public function addRaw(string $pattern): self
    {
        if (str_starts_with($pattern, './')) {
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
     */
    public function setSourceDir(string $sourceDir): self
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
    public function setOutputDir(string $outputDir): self
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
    public function setPermalink(string $permalink): self
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
     */
    public function setDefaultFormatter(string $defaultFormatter): self
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
