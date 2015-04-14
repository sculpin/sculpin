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

/**
 * Proxy source
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class ProxySource implements SourceInterface
{
    /**
     * Source
     *
     * @var SourceInterface
     */
    protected $source;

    /**
     * Constructor
     *
     * @param SourceInterface $source
     */
    public function __construct(SourceInterface $source)
    {
        $this->source = $source;
    }

    /**
     * {@inheritdoc}
     */
    public function sourceId()
    {
        return $this->source->sourceId();
    }

    /**
     * {@inheritdoc}
     */
    public function isRaw()
    {
        return $this->source->isRaw();
    }

    /**
     * {@inheritdoc}
     */
    public function canBeFormatted()
    {
        return $this->source->isRaw();
    }

    /**
     * {@inheritdoc}
     */
    public function hasChanged()
    {
        return $this->source->hasChanged();
    }

    /**
     * {@inheritdoc}
     */
    public function setHasChanged()
    {
        return $this->source->setHasChanged();
    }

    /**
     * {@inheritdoc}
     */
    public function setHasNotChanged()
    {
        return $this->source->setHasNotChanged();
    }

    /**
     * {@inheritdoc}
     */
    public function permalink()
    {
        return $this->source->permalink();
    }

    /**
     * {@inheritdoc}
     */
    public function setPermalink(PermalinkInterface $permalink)
    {
        return $this->source->setPermalink($permalink);
    }

    /**
     * {@inheritdoc}
     */
    public function useFileReference()
    {
        return $this->source->useFileReference();
    }

    /**
     * {@inheritdoc}
     */
    public function file()
    {
        return $this->source->file();
    }

    /**
     * {@inheritdoc}
     */
    public function content()
    {
        return $this->source->content();
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content = null)
    {
        return $this->source->setContent($content);
    }

    /**
     * {@inheritdoc}
     */
    public function formattedContent()
    {
        return $this->source->formattedContent();
    }

    /**
     * {@inheritdoc}
     */
    public function setFormattedContent($formattedContent = null)
    {
        return $this->source->setFormattedContent($formattedContent);
    }

    /**
     * {@inheritdoc}
     */
    public function relativePathname()
    {
        return $this->source->relativePathname();
    }

    /**
     * {@inheritdoc}
     */
    public function filename()
    {
        return $this->source->filename();
    }

    /**
     * {@inheritdoc}
     */
    public function data()
    {
        return $this->source->data();
    }

    /**
     * {@inheritdoc}
     */
    public function isGenerator()
    {
        return $this->source->data();
    }

    /**
     * {@inheritdoc}
     */
    public function setIsGenerator()
    {
        return $this->source->setIsGenerator();
    }

    /**
     * {@inheritdoc}
     */
    public function setIsNotGenerator()
    {
        return $this->source->setIsNotGenerator();
    }

    /**
     * {@inheritdoc}
     */
    public function isGenerated()
    {
        return $this->source->isGenerated();
    }

    /**
     * {@inheritdoc}
     */
    public function setIsGenerated()
    {
        return $this->source->setIsGenerated();
    }

    /**
     * {@inheritdoc}
     */
    public function setIsNotGenerated()
    {
        return $this->source->setIsNotGenerated();
    }

    /**
     * {@inheritdoc}
     */
    public function shouldBeSkipped()
    {
        $this->source->shouldBeSkipped();
    }

    /**
     * {@inheritdoc}
     */
    public function setShouldBeSkipped()
    {
        $this->source->setShouldBeSkipped();
    }

    /**
     * {@inheritdoc}
     */
    public function setShouldNotBeSkipped()
    {
        $this->source->setShouldNotBeSkipped();
    }

    /**
     * {@inheritdoc}
     */
    public function forceReprocess()
    {
        return $this->source->forceReprocess();
    }

    /**
     * {@inheritdoc}
     */
    public function url()
    {
        return $this->source->url();
    }

    /**
     * {@inheritdoc}
     */
    public function duplicate($newSourceId)
    {
        return $this->source->duplicate($newSourceId);
    }
}
