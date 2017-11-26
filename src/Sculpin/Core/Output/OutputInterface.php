<?php declare(strict_types=1);

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
use Symfony\Component\Finder\SplFileInfo;

/**
 * Output Interface
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
interface OutputInterface
{
    /**
     * Unique ID
     */
    public function outputId(): string;

    /**
     * Pathname (relative)
     */
    public function pathname(): string;

    /**
     * Suggested permalink
     */
    public function permalink(): PermalinkInterface;

    /**
     * Has a file reference?
     */
    public function hasFileReference(): bool;

    /**
     * File reference. (if hasFileReference)
     *
     * @return SplFileInfo|null
     */
    public function file(): ?SplFileInfo;

    /**
     * Formatted content (if not hasFileReference)
     *
     * @return string|null
     */
    public function formattedContent(): ?string;
}
