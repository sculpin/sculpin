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
use sculpin\source\ISource;

class ConvertSourceEvent extends Event {
    
    /**
     * Source
     * @var \sculpin\source\ISource
     */
    protected $source;
    
    /**
     * Converter
     * @var string
     */
    protected $converter;
    
    /**
     * Constructor
     * @param Sculpin $sculpin
     * @param ISource $source
     * @param string $converter
     */
    public function __construct(Sculpin $sculpin, ISource $source, $converter)
    {
        parent::__construct($sculpin);
        $this->source = $source;
        $this->converter = $converter;
    }
    
    /**
     * Source
     * 
     * @return \sculpin\source\ISource
     */
    public function source()
    {
        return $this->source;
    }
    
    /**
     * Converter
     * 
     * @return string
     */
    public function converter()
    {
        return $this->converter;
    }
    
    /**
     * Test if represented source file is converted by requested converter
     * 
     * @param string $converter
     * @return boolean
     */
    public function isConvertedBy($converter)
    {
        return $converter == $this->converter;
    }
    
    /**
     * Test if represented source  is formatted by requested formatter
     * 
     * @param string $formatter
     * @return boolean
     */
    public function isFormattedBy($formatter)
    {
        return $this->sculpin->deriveSourceFormatter($this->source) == $formatter;
    }
    
    /**
     * Test if represented source is converted and formatted by requested converter and formatter
     * 
     * @param string $converter
     * @param string $formatter
     * @return boolean
     */
    public function isHandledBy($converter, $formatter)
    {
        return $this->isConvertedBy($converter) and $this->isFormattedBy($formatter);
    }
}
