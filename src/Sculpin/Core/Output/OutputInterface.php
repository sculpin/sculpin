<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Output;

use Sculpin\Core\Permalink\PermalinkInterface;

/**
 * Output Interface
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
interface OutputInterface
{
    /**
     * Unique ID
     *
     * @return string
     */
    public function outputId();

    /**
     * Pathname (relative)
     *
     * @return string
     */
    public function pathname();

    /**
     * Suggested permalink
     *
     * @return PermalinkInterface
     */
    public function permalink();

    /**
     * Has a file reference?
     *
     * @return boolean
     */
    public function hasFileReference();

    /**
     * File reference. (if hasFileReference)
     *
     * @return \Symfony\Component\Finder\SplFileInfo
     */
    public function file();

    /**
     * Formatted content (if not hasFileReference)
     *
     * @return string
     */
    public function formattedContent();
}
