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
     * Since time
     *
     * @var string
     */
    protected $sinceTime;

    /**
     * Constructor.
     *
     * @param Configuration $configuration Configuration
     * @param string        $sourceDir     Source directory
     * @param FinderFactory $finderFactory Finder factory
     */
    public function __construct(Configuration $configuration, $sourceDir, FinderFactory $finderFactory = null)
    {
        $this->configuration = $configuration;
        $this->sourceDir = $sourceDir;
        $this->finderFactory = $finderFactory ?: new FinderFactory;
        $this->sinceTime = '1970-01-01T00:00:00Z';
    }

    /**
     * {@inheritdoc}
     */
    public function refresh(SourceSet $sourceSet)
    {
        $sinceTimeLast = $this->sinceTime;

        $files = $this
            ->finderFactory->create()
            ->files()
            ->ignoreVCS(true)
            ->date('>= '.$sinceTimeLast)
            ->in($this->sourceDir);

        foreach ($files as $file) {
            $isRaw = false; // UPDATE
            $source = new FileSource($file, $isRaw, true);
            $sourceSet->mergeSource($source);
        }
    }
}
