<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\source;

use sculpin\permalink\IPermalink;

interface ISource
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
     * @return \sculpin\permalink\IPermalink
     */
    public function permalink();

    /**
     * Set permalink
     * 
     * @param IPermalink $permalink
     */
    public function setPermalink(IPermalink $permalink);
    
    /**
     * Use file reference reference instead of string content
     * 
     * @return boolean
     */
    public function useFileReference();
    
    /**
     * File reference. (if use file reference)
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
     * @return \sculpin\configuration\Configuration
     */
    public function data();

    /**
     * Force source to be reprocessed
     */
    public function forceReprocess();
}
