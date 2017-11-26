<?php declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Source;

use Dflydev\DotAccessConfiguration\Configuration;
use Sculpin\Core\Permalink\PermalinkInterface;
use SplFileInfo;

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
     */
    public function __construct(SourceInterface $source)
    {
        $this->source = $source;
    }

    /**
     * {@inheritdoc}
     */
    public function sourceId(): string
    {
        return $this->source->sourceId();
    }

    /**
     * {@inheritdoc}
     */
    public function isRaw(): bool
    {
        return $this->source->isRaw();
    }

    /**
     * {@inheritdoc}
     */
    public function canBeFormatted(): bool
    {
        return $this->source->isRaw();
    }

    /**
     * {@inheritdoc}
     */
    public function hasChanged(): bool
    {
        return $this->source->hasChanged();
    }

    /**
     * {@inheritdoc}
     */
    public function setHasChanged(): bool
    {
        $this->source->setHasChanged();
    }

    /**
     * {@inheritdoc}
     */
    public function setHasNotChanged(): bool
    {
        $this->source->setHasNotChanged();
    }

    /**
     * {@inheritdoc}
     */
    public function permalink(): PermalinkInterface
    {
        return $this->source->permalink();
    }

    /**
     * {@inheritdoc}
     */
    public function setPermalink(PermalinkInterface $permalink)
    {
        $this->source->setPermalink($permalink);
    }

    /**
     * {@inheritdoc}
     */
    public function useFileReference(): bool
    {
        return $this->source->useFileReference();
    }

    /**
     * {@inheritdoc}
     */
    public function file(): SplFileInfo
    {
        return $this->source->file();
    }

    /**
     * {@inheritdoc}
     */
    public function content(): string
    {
        return $this->source->content();
    }

    /**
     * {@inheritdoc}
     */
    public function setContent(string $content = null)
    {
        $this->source->setContent($content);
    }

    /**
     * {@inheritdoc}
     */
    public function formattedContent(): string
    {
        return $this->source->formattedContent();
    }

    /**
     * {@inheritdoc}
     */
    public function setFormattedContent(string $formattedContent = null)
    {
        $this->source->setFormattedContent($formattedContent);
    }

    /**
     * {@inheritdoc}
     */
    public function relativePathname(): string
    {
        return $this->source->relativePathname();
    }

    /**
     * {@inheritdoc}
     */
    public function filename(): string
    {
        return $this->source->filename();
    }

    /**
     * {@inheritdoc}
     */
    public function data(): Configuration
    {
        return $this->source->data();
    }

    /**
     * {@inheritdoc}
     */
    public function isGenerator(): bool
    {
        return $this->source->isGenerator();
    }

    /**
     * {@inheritdoc}
     */
    public function setIsGenerator()
    {
        $this->source->setIsGenerator();
    }

    /**
     * {@inheritdoc}
     */
    public function setIsNotGenerator()
    {
        $this->source->setIsNotGenerator();
    }

    /**
     * {@inheritdoc}
     */
    public function isGenerated(): bool
    {
        return $this->source->isGenerated();
    }

    /**
     * {@inheritdoc}
     */
    public function setIsGenerated()
    {
        $this->source->setIsGenerated();
    }

    /**
     * {@inheritdoc}
     */
    public function setIsNotGenerated()
    {
        $this->source->setIsNotGenerated();
    }

    /**
     * {@inheritdoc}
     */
    public function shouldBeSkipped(): bool
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
        $this->source->forceReprocess();
    }

    /**
     * {@inheritdoc}
     */
    public function url(): string
    {
        return $this->source->url();
    }

    /**
     * {@inheritdoc}
     */
    public function duplicate(string $newSourceId): SourceInterface
    {
        return $this->source->duplicate($newSourceId);
    }
}
