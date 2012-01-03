<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\event;

use sculpin\Sculpin;

use Symfony\Component\EventDispatcher\Event as BaseEvent;

class Event extends BaseEvent {
    
    /**
     * Sculpin
     * @var Sculpin
     */
    public $sculpin;

    /**
     * Constructor
     * @param Sculpin $sculpin
     */
    public function __construct(Sculpin $sculpin)
    {
        $this->sculpin = $sculpin;
    }
    
    /**
     * Convenience method
     * @return \sculpin\configuration\Configuration
     */
    public function configuration()
    {
        return $this->sculpin->configuration();
    }

}