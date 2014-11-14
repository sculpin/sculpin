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
use Dflydev\DotAccessConfiguration\Configuration as Data;

/**
 * Abstract Source.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
abstract class AbstractSource implements SourceInterface
{
    /**
     * Source ID
     *
     * @var string
     */
    protected $sourceId;

    /**
     * Is raw?
     *
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
     * Formatted content
     *
     * @var string
     */
    protected $formattedContent;

    /**
     * Data
     *
     * @var Data
     */
    protected $data;

    /**
     * Has changed?
     *
     * @var boolean
     */
    protected $hasChanged;

    /**
     * Permalink
     *
     * @var PermalinkInterface
     */
    protected $permalink;

    /**
     * File
     *
     * @var \SplFileInfo
     */
    protected $file;

    /**
     * Relative pathname
     *
     * @var string
     */
    protected $relativePathname;

    /**
     * Filename
     *
     * @var string
     */
    protected $filename;

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
     * Is a generator?
     *
     * @var boolean
     */
    protected $isGenerator = false;

    /**
     * Is generated?
     *
     * @var boolean
     */
    protected $isGenerated = false;

    /**
     * Should be skipped?
     *
     * @var boolean
     */
    protected $shouldBeSkipped = false;

    /**
     * Initialize source
     *
     * @param bool $hasChanged Has the Source changed?
     */
    protected function init($hasChanged = null)
    {
        if (null !== $hasChanged) {
            $this->hasChanged = $hasChanged;
        }
        $this->shouldBeSkipped = false;
    }

    /**
     * {@inheritdoc}
     */
    public function sourceId()
    {
        return $this->sourceId;
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
    public function formattedContent()
    {
        return $this->formattedContent;
    }

    /**
     * {@inheritdoc}
     */
    public function setFormattedContent($formattedContent = null)
    {
        $this->formattedContent = $formattedContent;
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
    public function isGenerator()
    {
        return $this->isGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsGenerator()
    {
        $this->isGenerator = true;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsNotGenerator()
    {
        $this->isGenerator = false;
    }

    /**
     * {@inheritdoc}
     */
    public function isGenerated()
    {
        return $this->isGenerated;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsGenerated()
    {
        $this->isGenerated = true;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsNotGenerated()
    {
        $this->isGenerated = false;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldBeSkipped()
    {
        return $this->shouldBeSkipped;
    }

    /**
     * {@inheritdoc}
     */
    public function setShouldBeSkipped()
    {
        $this->shouldBeSkipped = true;
    }

    /**
     * {@inheritdoc}
     */
    public function setShouldNotBeSkipped()
    {
        $this->shouldBeSkipped = false;
    }

    /**
     * {@inheritdoc}
     */
    public function forceReprocess()
    {
        $this->init(true);
    }

    /**
     * {@inheritdoc}
     */
    public function relativePathname()
    {
        return $this->relativePathname;
    }

    /**
     * {@inheritdoc}
     */
    public function filename()
    {
        return $this->filename;
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
    public function url()
    {
        return $this->permalink()->relativeUrlPath();
    }

    /**
     * {@inheritdoc}
     */
    public function duplicate($newSourceId, array $options = array())
    {
        return new MemorySource(
            $newSourceId,
            new Data($this->data->exportRaw()),
            isset($options['content']) ? $options['content'] : $this->content,
            isset($options['formattedContent']) ? $options['formattedContent'] : $this->formattedContent,
            isset($options['relativePathname']) ? $options['relativePathname'] : $this->relativePathname,
            isset($options['filename']) ? $options['filename'] : $this->filename,
            isset($options['file']) ? $options['file'] : $this->file,
            isset($options['isRaw']) ? $options['isRaw'] : $this->isRaw,
            isset($options['canBeFormatted']) ? $options['canBeFormatted'] : $this->canBeFormatted,
            isset($options['hasChanged']) ? $options['hasChanged'] : $this->hasChanged
        );
    }
}
