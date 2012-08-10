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

use Sculpin\Core\Permalink\PermalinkInterface;
use Symfony\Component\Finder\SplFileInfo;
use Dflydev\DotAccessConfiguration\Configuration;
use Dflydev\DotAccessConfiguration\YamlConfigurationBuilder;

/**
 * File Source.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class FileSource implements SourceInterface
{
    /**
     * Data Source
     *
     * @var DataSourceInterface
     */
    protected $dataSource;

    /**
     * File
     *
     * @var SplFileInfo
     */
    protected $file;

    /**
     * Is raw?
     * @var boolean
     */
    protected $isRaw;

    /**
     * Content
     *
     * @var string
     */
    protected $content;

    /**
     * Data
     *
     * @var \sculpin\configuration\Configuration
     */
    protected $data;

    /**
     * Permalink
     *
     * @var PermalinkInterface
     */
    protected $permalink;

    /**
     * Use file reference?
     *
     * @var boolean
     */
    protected $useFileReference = false;

    /**
     * Can be formatted?
     *
     * @var boolean
     */
    protected $canBeFormatted = false;

    /**
     * Constructor
     *
     * @param DataSourceInterface $dataSource Data Source
     * @param SplFileInfo         $file       File
     * @param bool                $isRaw      Should be treated as raw
     * @param bool                $hasChanged Has the file changed?
     */
    public function __construct(DataSourceInterface $dataSource, SplFileInfo $file, $isRaw, $hasChanged = false)
    {
        $this->dataSource = $dataSource;
        $this->file = $file;
        $this->isRaw = $isRaw;
        $this->hasChanged = $hasChanged;
        $this->init();
    }

    /**
     * Initialize source
     *
     * @param bool $hasChanged Has the file changed?
     */
    protected function init($hasChanged = null)
    {
        if (null !== $hasChanged) {
            $this->hasChanged = $hasChanged;
        }
        if ($this->isRaw) {
            $this->useFileReference = true;
            $this->data = new Configuration();
        } else {
            $finfo = finfo_open(FILEINFO_MIME);
            $mime = finfo_file($finfo, $this->file);
            if (substr($mime, 0, 4) == 'text') {
                // Only text files can be processed by Sculpin and since we
                // have to read them here we are going to ensure that we use
                // the content we read here instead of having someone else
                // read the file again later.
                $this->useFileReference = false;

                // Additionally, any text file is a candidate for formatting.
                $this->canBeFormatted = true;

                $content = file_get_contents($this->file);

                if (preg_match('/^\s*(?:---[\r\n]+|)(.+?)(?:---[\r\n]+)(.*?)$/s', $content, $matches)) {
                    $this->content = $matches[2];
                    if (preg_match('/^(\s*[-]+\s*|\s*)$/', $matches[1])) {
                        // There is nothing useful in the YAML front matter.
                        $this->data = new Configuration(array());
                    } else {
                        // There may be YAML frontmatter
                        try {
                            $builder = new YamlConfigurationBuilder($matches[1]);
                            $this->data = $builder->build();
                        } catch (\Exception $e) {
                            // Likely not actually YAML front matter available,
                            // treat the entire file as pure content.
                            $this->content = $content;
                            $this->data = new Configuration(array());
                        }
                    }
                } else {
                    $this->content = $content;
                    $this->data = new Configuration(array());
                    $this->canBeFormatted = false;
                }
            } else {
                $this->useFileReference = true;
                $this->data = new Configuration(array());
            }
        }
        if ($this->data->get('date')) {
            $this->data->set('calculatedDate', strtotime($this->data->get('date')));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sourceId()
    {
        return 'FileSource:'.$this->dataSource->dataSourceId().':'.$this->file->getRelativePathname();
    }

    /**
     * {@inheritdoc}
     */
    public function isRaw()
    {
        return $this->isRaw;
    }

    /**
     * {@inheritdoc}
     */
    public function content()
    {
        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content = null)
    {
        $this->content = $content;

        // If we are setting content, we are going to assume that we should
        // not be using file references on output.
        $this->useFileReference = false;
    }

    /**
     * {@inheritdoc}
     */
    public function relativePathname()
    {
        return $this->file->getRelativePathname();
    }

    /**
     * {@inheritdoc}
     */
    public function filename()
    {
        return $this->file->getFilename();
    }

    /**
     * {@inheritdoc}
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function file()
    {
        return $this->file;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChanged()
    {
        return $this->hasChanged;
    }

    /**
     * {@inheritdoc}
     */
    public function setHasChanged()
    {
        $this->hasChanged = true;
    }

    /**
     * {@inheritdoc}
     */
    public function setHasNotChanged()
    {
        $this->hasChanged = false;
    }

    /**
     * {@inheritdoc}
     */
    public function permalink()
    {
        return $this->permalink;
    }

    /**
     * {@inheritdoc}
     */
    public function setPermalink(PermalinkInterface $permalink)
    {
        $this->permalink = $permalink;
    }

    /**
     * {@inheritdoc}
     */
    public function useFileReference()
    {
        return $this->useFileReference;
    }

    /**
     * {@inheritdoc}
     */
    public function canBeFormatted()
    {
        return $this->canBeFormatted;
    }

    /**
     * {@inheritdoc}
     */
    public function forceReprocess()
    {
        $this->init(true);
    }
}
