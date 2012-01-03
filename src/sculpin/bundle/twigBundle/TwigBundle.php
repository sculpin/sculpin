<?php

/*
 * This file is a part of Sculpin.
 * 
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\bundle\twigBundle;

use sculpin\Sculpin;

use sculpin\event\InputFilesChangedEvent;

use sculpin\bundle\IBundle;

class TwigBundle implements IBundle {

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\EventDispatcher.EventSubscriberInterface::getSubscribedEvents()
     */
    static function getSubscribedEvents()
    {
        return array(Sculpin::EVENT_FORMAT => 'format');
    }
    
    public function format(InputFilesChangedEvent $event)
    {
        foreach ( array_merge($event->newFiles, $event->updatedFiles) as $inputFile ) {
            //print $inputFile->content() . "\n\n\n";
        }
    }

}