<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Source;

use dflydev\util\antPathMatcher\AntPathMatcher;
use Sculpin\Core\Configuration\Configuration;
use Sculpin\Core\Finder\FinderFactory;

/**
 * Filesystem Data Source.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class FilesystemDataSource implements DataSourceInterface
{
    /**
     * Configuration
     *
     * @var Configuration
     */
    protected $configuration;

    /**
     * Permalink factory
     *
     * @var SourcePermalinkFactory
     */
    protected $permalinkFactory;

    /**
     * Source directory
     *
     * @var string
     */
    protected $sourceDir;

    /**
     * Finder Factory
     *
     * @var FinderFactory
     */
    protected $finderFactory;

    /**
     * Path Matcher
     *
     * @var AntPathMatcher
     */
    protected $matcher;

    /**
     * Since time
     *
     * @var string
     */
    protected $sinceTime;

    /**
     * Constructor.
     *
     * @param Configuration  $configuration Configuration
     * @param string         $sourceDir     Source directory
     * @param FinderFactory  $finderFactory Finder factory
     * @param AntPathMatcher $matcher       Matcher
     */
    public function __construct(Configuration $configuration, $sourceDir, FinderFactory $finderFactory = null, AntPathMatcher $matcher = null)
    {
        $this->configuration = $configuration;
        $this->sourceDir = $sourceDir;
        $this->finderFactory = $finderFactory ?: new FinderFactory;
        $this->matcher = $matcher ?: new AntPathMatcher;
        $this->sinceTime = '1970-01-01T00:00:00Z';
    }

    /**
     * {@inheritdoc}
     */
    public function dataSourceId()
    {
        return 'FilesystemDataSource:'.$this->configuration->sourceDir();
    }

    /**
     * {@inheritdoc}
     */
    public function refresh(SourceSet $sourceSet)
    {
        $sinceTimeLast = $this->sinceTime;

        // We regenerate the whole site if an excluded file changes.
        $excludedFilesHaveChanged = false;

        $files = $this
            ->finderFactory->create()
            ->files()
            ->ignoreVCS(true)
            ->date('>= '.$sinceTimeLast)
            ->in($this->sourceDir);

        foreach ($files as $file) {
            foreach ($this->configuration->ignores() as $pattern) {
                if (!$this->matcher->isPattern($pattern)) {
                    continue;
                }
                if ($this->matcher->match($pattern, $file->getRelativePathname())) {
                    // Ignored files are completely ignored.
                    continue 2;
                }
            }
            foreach ($this->configuration->excludes() as $pattern) {
                if (!$this->matcher->isPattern($pattern)) {
                    continue;
                }
                if ($this->matcher->match($pattern, $file->getRelativePathname())) {
                    $excludedFilesHaveChanged = true;
                    continue 2;
                }
            }

            $isRaw = false;

            foreach ($this->configuration->raws() as $pattern) {
                if (!$this->matcher->isPattern($pattern)) {
                    continue;
                }
                if ($this->matcher->match($pattern, $file->getRelativePathname())) {
                    $isRaw = true;
                    break;
                }
            }

            $source = new FileSource($this, $file, $isRaw, true);
            $sourceSet->mergeSource($source);
        }

        if ($excludedFilesHaveChanged) {
            // If any of the exluded files have changed we should
            // mark all of the sources as having changed.
            foreach ($sourceSet->allSources() as $source) {
                $source->setHasChanged();
            }
        }
    }
}
