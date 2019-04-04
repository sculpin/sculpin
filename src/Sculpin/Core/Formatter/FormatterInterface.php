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

namespace Sculpin\Core\Formatter;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
interface FormatterInterface
{
    /**
     * Format the input blocks
     */
    public function formatBlocks(FormatContext $formatContext): array;

    /**
     * Format an entire page
     *
     * @return string
     */
    public function formatPage(FormatContext $formatContext): string;

    /**
     * Reset
     *
     * Provides formatters with the ability to do things like clear cache
     * (if applicable) or do anything else they need to do after having
     * run once.
     */
    public function reset();
}
