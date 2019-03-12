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

namespace Sculpin\Core\Source;

use Dflydev\DotAccessConfiguration\ConfigurationInterface;
use Symfony\Component\Finder\Finder;
use dflydev\util\antPathMatcher\AntPathMatcher;
use Sculpin\Core\SiteConfiguration\SiteConfigurationFactory;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
final class ConfigFilesystemDataSource implements DataSourceInterface
{
    /**
     * @var string
     */
    private $sourceDir;

    /**
     * @var ConfigurationInterface
     */
    private $siteConfiguration;

    /**
     * @var SiteConfigurationFactory
     */
    private $siteConfigurationFactory;

    /**
     * @var AntPathMatcher
     */
    private $pathMatcher;

    /**
     * @var string
     */
    private $sinceTime;

    public function __construct(
        string $sourceDir,
        ConfigurationInterface $siteConfiguration,
        SiteConfigurationFactory $siteConfigurationFactory,
        AntPathMatcher $pathMatcher = null
    ) {
        $this->sourceDir = $sourceDir;
        $this->siteConfiguration = $siteConfiguration;
        $this->siteConfigurationFactory = $siteConfigurationFactory;
        $this->pathMatcher = $pathMatcher ?: new AntPathMatcher;
        $this->sinceTime = '1970-01-01T00:00:00Z';
    }

    /**
     * {@inheritdoc}
     */
    public function dataSourceId(): string
    {
        // This is not really needed since we are not going to
        // ever create actual sources.
        return 'ConfigFilesystemDataSource:'.$this->sourceDir;
    }

    /**
     * {@inheritdoc}
     */
    public function refresh(SourceSet $sourceSet): void
    {
        if (! is_dir($this->sourceDir)) {
            return;
        }

        $sinceTimeLast = $this->sinceTime;

        $this->sinceTime = date('c');

        // We regenerate the whole site if any config file changes.
        $configFilesHaveChanged = false;

        $files = Finder::create()
            ->files()
            ->name('sculpin_site*.yml')
            ->date('>='.$sinceTimeLast)
            ->in($this->sourceDir);

        $sinceTimeLastSeconds = strtotime($sinceTimeLast);

        /** @var SplFileInfo $file */
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
