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

use Dflydev\DotAccessConfiguration\Configuration as Data;

/**
 * Memory Source.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class MemorySource extends AbstractSource
{
    /**
     * Constructor
     *
     * @param string       $sourceId         Source ID
     * @param Data         $data             Data
     * @param string       $content          Content
     * @param string       $formattedContent Formatted content
     * @param string       $relativePathname Relative Pathname
     * @param string       $filename         Filename
     * @param \SplFileInfo $file             File
     * @param bool         $isRaw            Is raw?
     * @param bool         $canBeFormatted   Can be formatted?
     * @param bool         $hasChanged       Has changed?
     */
    public function __construct(
        $sourceId,
        Data $data,
        $content,
        $formattedContent,
        $relativePathname,
        $filename,
        $file,
        $isRaw,
        $canBeFormatted,
        $hasChanged
    ) {
        $this->sourceId = $sourceId;
        $this->data = $data;
        $this->content = $content;
        $this->formattedContent = $formattedContent;
        $this->relativePathname = $relativePathname;
        $this->filename = $filename;
        $this->file = $file;
        $this->isRaw = $isRaw;
        $this->canBeFormatted = $canBeFormatted;
        $this->hasChanged = $hasChanged;
    }
}
