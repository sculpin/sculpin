<?php

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
 * Formatter interface
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
interface FormatterInterface
{
    /**
     * Format the input blocks
     *
     * @param FormatContext $formatContext Format context
     *
     * @return array
     */
    public function formatBlocks(FormatContext $formatContext);

    /**
     * Format an entire page
     *
     * @param FormatContext $formatContext Format context
     *
     * @return string
     */
    public function formatPage(FormatContext $formatContext);

    /**
     * Reset
     *
     * Provides formatters with the ability to do things like clear cache
     * (if applicable) or do anything else they need to do after having
     * run once.
     */
    public function reset();
}
