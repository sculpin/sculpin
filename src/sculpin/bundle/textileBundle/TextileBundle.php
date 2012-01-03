<?php

/*
 * This file is a part of Sculpin.
 * 
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\bundle\textileBundle;

use sculpin\bundle\IBundle;

class TextileBundle implements IBundle {
    
    /**
     * (non-PHPdoc)
     * @see Symfony\Component\EventDispatcher.EventSubscriberInterface::getSubscribedEvents()
     */
    static function getSubscribedEvents()
    {
        return array();
    }

}