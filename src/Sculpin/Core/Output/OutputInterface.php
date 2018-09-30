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
    public function outputId(): string;

    /**
     * Pathname (relative)
     *
     * @return string
     */
    public function pathname(): string;

    /**
     * Suggested permalink
     *
     * @return PermalinkInterface
     */
    public function permalink(): PermalinkInterface;

    /**
     * Has a file reference?
     *
     * @return boolean
     */
    public function hasFileReference(): bool;

    /**
     * File reference. (if hasFileReference)
     *
     * @return \SplFileInfo|null
     */
    public function file();

    /**
     * Formatted content (if not hasFileReference)
     *
     * @return string
     */
    public function formattedContent(): string;
}
