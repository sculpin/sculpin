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

use sculpin\Sculpin;

interface IFormatter {

    /**
     * Format the input blocks
     * @param SourceFile $inputFile
     * @return array
     */
    public function formatBlocks(Sculpin $sculpin, $template, $context);

    /**
     * Format an entire page
     * @param SourceFile $inputFile
     * @return string
     */
    public function formatPage(Sculpin $sculpin, $template, $context);

}