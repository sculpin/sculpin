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
use Dflydev\DotAccessConfiguration\Configuration;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
interface SourceInterface
{
    /**
     * @return String
     */
    public function sourceId(): string;

    /**
     * Whether this source is a raw source.
     */
    public function isRaw(): bool;

    /**
     * Whether this source can be formatted.
     */
    public function canBeFormatted(): bool;

    public function hasChanged(): bool;

    /**
     * Mark source as changed.
     */
    public function setHasChanged(): void;

    /**
     * Mark source as not changed.
     */
    public function setHasNotChanged(): void;

    public function permalink(): PermalinkInterface;

    public function setPermalink(PermalinkInterface $permalink);

    /**
     * Whether to use file reference reference instead of string content.
     */
    public function useFileReference(): bool;

    /**
     * File reference. (if useFileReference)
     */
    public function file(): \SplFileInfo;

    /**
     * Content (if not useFileReference)
     */
    public function content(): ?string;

    public function setContent(?string $content = null): void;

    /**
     * Formatted content (if not useFileReference)
     */
    public function formattedContent(): ?string;

    public function setFormattedContent(?string $formattedContent = null): void;

    public function relativePathname(): string;

    public function filename(): string;

    public function data(): Configuration;

    /**
     * Whether this source is a generator.
     */
    public function isGenerator(): bool;

    /**
     * Mark Source as being a generator.
     */
    public function setIsGenerator(): void;

    /**
     * Mark Source as not being a generator.
     */
    public function setIsNotGenerator(): void;

    /**
     * Whether source is generated (from a generator).
     */
    public function isGenerated(): bool;

    /**
     * Mark Source as being generated (by a generator).
     */
    public function setIsGenerated(): void;

    /**
     * Mark Source as not being generated (by a generator).
     */
    public function setIsNotGenerated(): void;

    /**
     * Whether this source should be skipped.
     */
    public function shouldBeSkipped(): bool;

    /**
     * Mark Source as being skipped.
     */
    public function setShouldBeSkipped(): void;

    /**
     * Mark Source as not being skipped.
     */
    public function setShouldNotBeSkipped(): void;

    /**
     * Force Source to be reprocessed.
     */
    public function forceReprocess(): void;

    /**
     * Get the URL for this source.
     *
     * Convenience method based on the permalink of this source.
     *
     * @return string
     */
    public function url(): string;

    public function duplicate(string $newSourceId): SourceInterface;
}
