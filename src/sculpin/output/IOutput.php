<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\output;

use Symfony\Component\Finder\SplFileInfo;

interface IOutput {
    
    /**
     * Unique ID
     * @return string
     */
    public function outputId();
    
    /**
     * Pathname (relative)
     * @return string 
     */
    public function pathname();
    
    /**
     * Can have a permalink
     * @return boolean
     */
    public function canHavePermalink();
    
    /**
     * Suggested permalink
     * @return string
     */
    public function permalink();
    
    /**
     * Has a file reference?
     * @return bool
     */
    public function hasFileReference();    
    
    /**
     * File reference. (if hasFileReference)
     * @return \Symfony\Component\Finder\SplFileInfo
     */
    public function file();

    /**
     * Content (if not hasFileReference)
     * @return string
     */
    public function content();
    
    /**
     * Date
     * @return integer
     */
    public function date();

    /**
     * Title
     * @return string
     */
    public function title();
    
}