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

use Dflydev\DotAccessConfiguration\ConfigurationInterface;
use Dflydev\Symfony\FinderFactory\FinderFactory;
use Dflydev\Symfony\FinderFactory\FinderFactoryInterface;
use dflydev\util\antPathMatcher\AntPathMatcher;
use Sculpin\Core\SiteConfiguration\SiteConfigurationFactory;

/**
 * Config Filesystem Data Source.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class ConfigFilesystemDataSource implements DataSourceInterface
{
    /**
     * Source directory
     *
     * @var string
     */
    protected $sourceDir;

    /**
     * Site configuration
     *
     * @var ConfigurationInterface
     */
    protected $siteConfiguration;

    /**
     * Site configuration factory
     *
     * @var string
     */
    protected $siteConfigurationFactory;

    /**
     * Finder Factory
     *
     * @var FinderFactoryInterface
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
     * @param string                   $sourceDir                Source directory
     * @param ConfigurationInterface   $siteConfiguration        Site Configuration
     * @param SiteConfigurationFactory $siteConfigurationFactory Site Configuration Factory
     * @param FinderFactoryInterface   $finderFactory            Finder Factory
     * @param AntPathMatcher           $matcher                  Matcher
     */
    public function __construct(
        $sourceDir,
        ConfigurationInterface $siteConfiguration,
        SiteConfigurationFactory $siteConfigurationFactory,
        FinderFactoryInterface $finderFactory = null,
        AntPathMatcher $matcher = null
    ) {
        $this->sourceDir = $sourceDir;
        $this->siteConfiguration = $siteConfiguration;
        $this->siteConfigurationFactory = $siteConfigurationFactory;
        $this->finderFactory = $finderFactory ?: new FinderFactory;
        $this->matcher = $matcher ?: new AntPathMatcher;
        $this->sinceTime = '1970-01-01T00:00:00Z';
    }

    /**
     * {@inheritdoc}
     */
    public function dataSourceId()
    {
        // This is not really needed since we are not going to
        // ever create actual sources.
        return 'ConfigFilesystemDataSource:'.$this->sourceDir;
    }

    /**
     * {@inheritdoc}
     */
    public function refresh(SourceSet $sourceSet)
    {
        if (! is_dir($this->sourceDir)) {
            return;
        }

        $sinceTimeLast = $this->sinceTime;

        $this->sinceTime = date('c');

        // We regenerate the whole site if any config file changes.
        $configFilesHaveChanged = false;

        $files = $this
            ->finderFactory->createFinder()
            ->files()
            ->name('sculpin_site*.yml')
            ->date('>='.$sinceTimeLast)
            ->in($this->sourceDir);

        $sinceTimeLastSeconds = strtotime($sinceTimeLast);

        foreach ($files as $file) {
            if ($sinceTimeLastSeconds > $file->getMTime()) {
                // This is a hack because Finder is actually incapable
                // of resolution down to seconds.
                //
                // Sometimes this may result in the file looking like it
                // has been modified twice in a row when it has not.
                continue;
            }

            $configFilesHaveChanged = true;

            break;
        }

        if ($configFilesHaveChanged) {
            $newConfig = $this->siteConfigurationFactory->create();
            $this->siteConfiguration->import($newConfig);

            // If any of the config files have changed we should
            // mark all of the sources as having changed.
            foreach ($sourceSet->allSources() as $source) {
                $source->setHasChanged();
            }
        }
    }
}
