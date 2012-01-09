<?php

/*
 * This file is a part of Sculpin.
 * 
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\bundle;

use sculpin\Sculpin;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface IBundle extends EventSubscriberInterface {
    
    /**
     * Initialize the bundle.
     */
    public function initBundle(Sculpin $sculpin);
    
    /**
     * Get a full path to a resource
     * @param string $partialPath
     */
    public function getResourcePath($partialPath);

}