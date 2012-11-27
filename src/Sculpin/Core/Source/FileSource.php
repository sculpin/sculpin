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
use Dflydev\DotAccessConfiguration\Configuration as Data;
use Dflydev\DotAccessConfiguration\YamlConfigurationBuilder as YamlDataBuilder;

/**
 * File Source.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class FileSource extends AbstractSource
{
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
        $this->sourceId = 'FileSource:'.$dataSource->dataSourceId().':'.$file->getRelativePathname();
        $this->relativePathname = $file->getRelativePathname();
        $this->filename = $file->getFilename();
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
        parent::init($hasChanged);

        if ($this->isRaw) {
            $this->useFileReference = true;
            $this->data = new Data;
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
                        $this->data = new Data;
                    } else {
                        // There may be YAML frontmatter
                        try {
                            $builder = new YamlDataBuilder($matches[1]);
                            $this->data = $builder->build();
                        } catch (\Exception $e) {
                            // Likely not actually YAML front matter available,
                            // treat the entire file as pure content.
                            $this->content = $content;
                            $this->data = new Data;
                        }
                    }
                } else {
                    $this->content = $content;
                    $this->data = new Data;
                    $this->canBeFormatted = false;
                }
            } else {
                $this->useFileReference = true;
                $this->data = new Data;
            }
        }
        if ($this->data->get('date')) {
            $this->data->set('calculatedDate', strtotime($this->data->get('date')));
        }
    }
}
