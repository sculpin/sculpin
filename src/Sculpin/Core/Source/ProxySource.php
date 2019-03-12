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

use Dflydev\DotAccessConfiguration\Configuration;
use Sculpin\Core\Permalink\PermalinkInterface;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
class ProxySource implements SourceInterface
{
    /**
     * @var SourceInterface
     */
    protected $source;

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
    public function setHasChanged(): void
    {
        $this->source->setHasChanged();
    }

    /**
     * {@inheritdoc}
     */
    public function setHasNotChanged(): void
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
    public function setPermalink(PermalinkInterface $permalink): void
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
    public function file(): \SplFileInfo
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
    public function setContent(?string $content = null): void
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
    public function setFormattedContent(?string $formattedContent = null): void
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
    public function setIsGenerator(): void
    {
        $this->source->setIsGenerator();
    }

    /**
     * {@inheritdoc}
     */
    public function setIsNotGenerator(): void
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
    public function setIsGenerated(): void
    {
        $this->source->setIsGenerated();
    }

    /**
     * {@inheritdoc}
     */
    public function setIsNotGenerated(): void
    {
        $this->source->setIsNotGenerated();
    }

    /**
     * {@inheritdoc}
     */
    public function shouldBeSkipped(): bool
    {
        return $this->source->shouldBeSkipped();
    }

    /**
     * {@inheritdoc}
     */
    public function setShouldBeSkipped(): void
    {
        $this->source->setShouldBeSkipped();
    }

    /**
     * {@inheritdoc}
     */
    public function setShouldNotBeSkipped(): void
    {
        $this->source->setShouldNotBeSkipped();
    }

    /**
     * {@inheritdoc}
     */
    public function forceReprocess(): void
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
    public function duplicate($newSourceId): SourceInterface
    {
        return $this->source->duplicate($newSourceId);
    }
}
