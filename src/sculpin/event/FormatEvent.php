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

use sculpin\formatter\FormatContext;

use sculpin\Sculpin;

class FormatEvent extends Event {
    
    /**
     * Format context
     * @var \sculpin\formatter\FormatContext
     */
    protected $formatContext;
    
    /**
     * Constructor
     * @param Sculpin $sculpin
     */
    public function __construct(Sculpin $sculpin, FormatContext $formatContext)
    {
        parent::__construct($sculpin);
        $this->formatContext = $formatContext;
    }
    
    /**
     * Format context
     * @return \sculpin\formatter\FormatContext
     */
    public function formatContext()
    {
        return $this->formatContext;
    }

}