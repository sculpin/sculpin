<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\converter;

interface IConverterContext {
    
    /**
     * Content
     * return @string
     */
    public function content();
    
    /**
     * Set content
     * @param string $content
     */
    public function setContent($content);

}