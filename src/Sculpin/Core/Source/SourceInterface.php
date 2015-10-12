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
    public function sourceId();

    /**
     * Represents a raw source
     *
     * @return boolean
     */
    public function isRaw();

    /**
     * Represents a source that can be formatted
     *
     * @return boolean
     */
    public function canBeFormatted();

    /**
     * Has changed
     *
     * @return boolean
     */
    public function hasChanged();

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
    public function permalink();

    /**
     * Set permalink
     *
     * @param PermalinkInterface $permalink
     */
    public function setPermalink(PermalinkInterface $permalink);

    /**
     * Use file reference reference instead of string content
     *
     * @return bool
     */
    public function useFileReference();

    /**
     * File reference. (if uses file reference)
     *
     * @return \SplFileInfo
     */
    public function file();

    /**
     * Content (if not use file reference)
     *
     * @return string
     */
    public function content();

    /**
     * Set content
     *
     * @param string|null $content
     */
    public function setContent($content = null);

    /**
     * Formatted content (if not use file reference)
     *
     * @return string
     */
    public function formattedContent();

    /**
     * Set formatted content
     *
     * @param string|null $formattedContent
     */
    public function setFormattedContent($formattedContent = null);

    /**
     * Relative pathname
     *
     * @return string
     */
    public function relativePathname();

    /**
     * Filename
     *
     * @return string
     */
    public function filename();

    /**
     * Data
     *
     * @return Configuration
     */
    public function data();

    /**
     * Source is a generator
     *
     * @return bool
     */
    public function isGenerator();

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
    public function isGenerated();

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
    public function shouldBeSkipped();

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
    public function url();

    /**
     * Duplicate the source
     *
     * @param string $newSourceId
     *
     * @return SourceInterface
     */
    public function duplicate($newSourceId);
}
