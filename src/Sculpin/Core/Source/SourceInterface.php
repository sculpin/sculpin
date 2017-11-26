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

/**
 * Source Interface.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
interface SourceInterface
{
    /**
     * Source ID
     *
     * @return string
     */
    public function sourceId(): String;

    /**
     * Represents a raw source
     *
     */
    public function isRaw(): bool;

    /**
     * Represents a source that can be formatted
     *
     */
    public function canBeFormatted(): bool;

    /**
     * Has changed
     *
     */
    public function hasChanged(): bool;

    /**
     * Mark source as changed
     */
    public function setHasChanged(): void;

    /**
     * Mark source as not changed
     */
    public function setHasNotChanged(): void;

    /**
     * Permalink
     *
     */
    public function permalink(): PermalinkInterface;

    /**
     * Set permalink
     */
    public function setPermalink(PermalinkInterface $permalink): void;

    /**
     * Use file reference reference instead of string content
     *
     */
    public function useFileReference(): bool;

    /**
     * File reference. (if uses file reference)
     *
     */
    public function file(): \SplFileInfo;

    /**
     * Content (if not use file reference)
     *
     */
    public function content(): string;

    /**
     * Set content
     *
     */
    public function setContent(?string $content = null): void;

    /**
     * Formatted content (if not use file reference)
     *
     * @return string|null
     */
    public function formattedContent(): ?string;

    /**
     * Set formatted content
     *
     */
    public function setFormattedContent(?string $formattedContent = null): void;

    /**
     * Relative pathname
     *
     */
    public function relativePathname(): string;

    /**
     * Filename
     *
     */
    public function filename(): string;

    /**
     * Data
     *
     */
    public function data(): Configuration;

    /**
     * Source is a generator
     *
     */
    public function isGenerator(): bool;

    /**
     * Mark Source as being a generator
     */
    public function setIsGenerator(): void;

    /**
     * Mark Source as not being a generator
     */
    public function setIsNotGenerator(): void;

    /**
     * Source is generated (from a generator)
     *
     */
    public function isGenerated(): bool;

    /**
     * Mark Source as being generated (by a generator)
     */
    public function setIsGenerated(): void;

    /**
     * Mark Source as not being generated (by a generator)
     */
    public function setIsNotGenerated(): void;

    /**
     * Source should be skipped
     *
     */
    public function shouldBeSkipped(): bool;

    /**
     * Mark Source as being skipped
     */
    public function setShouldBeSkipped(): void;

    /**
     * Mark Source as not being skipped
     */
    public function setShouldNotBeSkipped(): void;

    /**
     * Force Source to be reprocessed
     */
    public function forceReprocess(): void;

    /**
     * URL
     *
     * Convenience method.
     *
     */
    public function url(): string;

    /**
     * Duplicate the source
     *
     *
     */
    public function duplicate(string $newSourceId): SourceInterface;
}
