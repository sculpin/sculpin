<?php

/*
 * This file is a part of Sculpin.
 * 
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\bundle\postsBundle;

use sculpin\event\InputFilesChangedEvent;

use sculpin\Sculpin;

use sculpin\bundle\IBundle;

class PostsBundle implements IBundle {
    
    /**
     * (non-PHPdoc)
     * @see Symfony\Component\EventDispatcher.EventSubscriberInterface::getSubscribedEvents()
     */
    static function getSubscribedEvents()
    {
        return array(Sculpin::EVENT_INPUT_FILES_CHANGED => 'inputFilesChanged');
    }
    
    public function inputFilesChanged(InputFilesChangedEvent $event) {
    }

}