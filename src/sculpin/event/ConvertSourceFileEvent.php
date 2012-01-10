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

use sculpin\source\SourceFile;

use sculpin\formatter\FormatContext;

use sculpin\Sculpin;

class ConvertSourceFileEvent extends Event {
    
    /**
     * Source file
     * @var \sculpin\source\SourceFile
     */
    protected $sourceFile;
    
    /**
     * Converter
     * @var string
     */
    protected $converter;
    
    /**
     * Constructor
     * @param Sculpin $sculpin
     * @param SourceFile $sourceFile
     * @param string $converter
     */
    public function __construct(Sculpin $sculpin, SourceFile $sourceFile, $converter)
    {
        parent::__construct($sculpin);
        $this->sourceFile = $sourceFile;
        $this->converter = $converter;
    }
    
    /**
     * Source file
     * @return \sculpin\source\SourceFile
     */
    public function sourceFile()
    {
        return $this->sourceFile;
    }
    
    /**
     * Converter
     * @return string
     */
    public function converter()
    {
        return $this->converter;
    }

}