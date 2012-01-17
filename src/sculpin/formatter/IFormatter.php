<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\formatter;

use sculpin\formatter\FormatContext;

use sculpin\Sculpin;

interface IFormatter {

    /**
     * Format the input blocks
     * @param Sculpin $sculpin
     * @param FormatContext $formatContext
     * @return array
     */
    public function formatBlocks(Sculpin $sculpin, FormatContext $formatContext);

    /**
     * Format an entire page
     * @param Sculpin $sculpin
     * @param FormatContext $formatContext
     * @return string
     */
    public function formatPage(Sculpin $sculpin, FormatContext $formatContext);
    
    /**
     * Reset formatter
     * 
     * Provides formatters with the ability to do things like clear cache
     * (if applicable) or do anything else they need to do after having
     * run once.
     */
    public function resetFormatter();

}