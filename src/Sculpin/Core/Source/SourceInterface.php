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

use Sculpin\Core\Configuration\Configuration;
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
     * @return String
     */
    public function sourceId(): String;

    /**
     * Represents a raw source
     *
     * @return boolean
     */
    public function isRaw(): bool;

    /**
     * Represents a source that can be formatted
     *
     * @return boolean
     */
    public function canBeFormatted(): bool;

    /**
     * Has changed
     *
     * @return boolean
     */
    public function hasChanged(): bool;

    /**
     * Mark source as changed
     */
    public function setHasChanged();

    /**
     * Mark source as not changed
     */
    public function setHasNotChanged();

    /**
     * Permalink
     *
     * @return PermalinkInterface
     */
    public function permalink(): PermalinkInterface;

    /**
     * Set permalink
     *
     */
    public function setPermalink(PermalinkInterface $permalink);

    /**
     * Use file reference reference instead of string content
     *
     * @return bool
     */
    public function useFileReference(): bool;

    /**
     * File reference. (if uses file reference)
     *
     * @return \SplFileInfo
     */
    public function file(): \SplFileInfo;

    /**
     * Content (if not use file reference)
     *
     * @return string
     */
    public function content(): string;

    /**
     * Set content
     *
     * @param string|null $content
     */
    public function setContent(string $content = null);

    /**
     * Formatted content (if not use file reference)
     *
     * @return string
     */
    public function formattedContent(): string;

    /**
     * Set formatted content
     *
     * @param string|null $formattedContent
     */
    public function setFormattedContent(string $formattedContent = null);

    /**
     * Relative pathname
     *
     * @return string
     */
    public function relativePathname(): string;

    /**
     * Filename
     *
     * @return string
     */
    public function filename(): string;

    /**
     * Data
     *
     * @return Configuration
     */
    public function data(): Configuration;

    /**
     * Source is a generator
     *
     * @return bool
     */
    public function isGenerator(): bool;

    /**
     * Mark Source as being a generator
     */
    public function setIsGenerator();

    /**
     * Mark Source as not being a generator
     */
    public function setIsNotGenerator();

    /**
     * Source is generated (from a generator)
     *
     * @return bool
     */
    public function isGenerated(): bool;

    /**
     * Mark Source as being generated (by a generator)
     */
    public function setIsGenerated();

    /**
     * Mark Source as not being generated (by a generator)
     */
    public function setIsNotGenerated();

    /**
     * Source should be skipped
     *
     * @return bool
     */
    public function shouldBeSkipped(): bool;

    /**
     * Mark Source as being skipped
     */
    public function setShouldBeSkipped();

    /**
     * Mark Source as not being skipped
     */
    public function setShouldNotBeSkipped();

    /**
     * Force Source to be reprocessed
     */
    public function forceReprocess();

    /**
     * URL
     *
     * Convenience method.
     *
     * @return string
     */
    public function url(): string;

    /**
     * Duplicate the source
     *
     * @param string $newSourceId
     *
     * @return SourceInterface
     */
    public function duplicate(string $newSourceId): SourceInterface;
}
