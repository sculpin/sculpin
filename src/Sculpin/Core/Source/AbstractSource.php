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

use Sculpin\Core\Permalink\PermalinkInterface;
use Dflydev\DotAccessConfiguration\Configuration as Data;
use Sculpin\Core\Source\SourceInterface;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
abstract class AbstractSource implements SourceInterface
{
    /**
     * @var string
     */
    protected $sourceId;

    /**
     * @var boolean
     */
    protected $isRaw;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $formattedContent;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @var boolean
     */
    protected $hasChanged;

    /**
     * @var PermalinkInterface
     */
    protected $permalink;

    /**
     * @var \SplFileInfo
     */
    protected $file;

    /**
     * @var string
     */
    protected $relativePathname;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var boolean
     */
    protected $useFileReference = false;

    /**
     * @var boolean
     */
    protected $canBeFormatted = false;

    /**
     * @var boolean
     */
    protected $isGenerator = false;

    /**
     * @var boolean
     */
    protected $isGenerated = false;

    /**
     * @var boolean
     */
    protected $shouldBeSkipped = false;

    protected function init(bool $hasChanged = false)
    {
        if ($hasChanged) {
            $this->hasChanged = $hasChanged;
        }
        $this->shouldBeSkipped = false;
    }

    /**
     * {@inheritdoc}
     */
    public function sourceId(): string
    {
        return $this->sourceId;
    }

    /**
     * {@inheritdoc}
     */
    public function isRaw(): bool
    {
        return $this->isRaw;
    }

    /**
     * {@inheritdoc}
     */
    public function content(): ?string
    {
        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function setContent(?string $content = null): void
    {
        $this->content = $content;

        // If we are setting content, we are going to assume that we should
        // not be using file references on output.
        $this->useFileReference = false;
    }

    /**
     * {@inheritdoc}
     */
    public function formattedContent(): ?string
    {
        return $this->formattedContent;
    }

    /**
     * {@inheritdoc}
     */
    public function setFormattedContent(?string $formattedContent = null): void
    {
        $this->formattedContent = $formattedContent;
    }

    /**
     * {@inheritdoc}
     */
    public function data(): Data
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChanged(): bool
    {
        return $this->hasChanged;
    }

    /**
     * {@inheritdoc}
     */
    public function setHasChanged(): void
    {
        $this->hasChanged = true;
    }

    /**
     * {@inheritdoc}
     */
    public function setHasNotChanged(): void
    {
        $this->hasChanged = false;
    }

    /**
     * {@inheritdoc}
     */
    public function permalink(): PermalinkInterface
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
    public function useFileReference(): bool
    {
        return $this->useFileReference;
    }

    /**
     * {@inheritdoc}
     */
    public function canBeFormatted(): bool
    {
        return $this->canBeFormatted;
    }

    /**
     * {@inheritdoc}
     */
    public function isGenerator(): bool
    {
        return $this->isGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsGenerator(): void
    {
        $this->isGenerator = true;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsNotGenerator(): void
    {
        $this->isGenerator = false;
    }

    /**
     * {@inheritdoc}
     */
    public function isGenerated(): bool
    {
        return $this->isGenerated;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsGenerated(): void
    {
        $this->isGenerated = true;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsNotGenerated(): void
    {
        $this->isGenerated = false;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldBeSkipped(): bool
    {
        return $this->shouldBeSkipped;
    }

    /**
     * {@inheritdoc}
     */
    public function setShouldBeSkipped(): void
    {
        $this->shouldBeSkipped = true;
    }

    /**
     * {@inheritdoc}
     */
    public function setShouldNotBeSkipped(): void
    {
        $this->shouldBeSkipped = false;
    }

    /**
     * {@inheritdoc}
     */
    public function forceReprocess(): void
    {
        $this->init(true);
    }

    /**
     * {@inheritdoc}
     */
    public function relativePathname(): string
    {
        return $this->relativePathname;
    }

    /**
     * {@inheritdoc}
     */
    public function filename(): string
    {
        return $this->filename;
    }

    /**
     * Mark source as can be formatted
     */
    public function setCanBeFormatted(): void
    {
        $this->canBeFormatted = true;
    }

    /**
     * {@inheritdoc}
     */
    public function file(): \SplFileInfo
    {
        return $this->file;
    }

    /**
     * {@inheritdoc}
     */
    public function url(): string
    {
        return $this->permalink()->relativeUrlPath();
    }

    /**
     * {@inheritdoc}
     */
    public function duplicate(string $newSourceId, array $options = []): SourceInterface
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
