<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\permalink;

interface IPermalink
{
    /**
     * Relative file path
     * @return string
     */
    public function relativeFilePath();
    
    /**
     * Relative URL path
     * @return string
     */
    public function relativeUrlPath();
}
