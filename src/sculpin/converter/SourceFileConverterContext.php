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

use sculpin\source\SourceFile;

class SourceFileConverterContext implements IConverterContext {

    /**
     * Source file
     * @var SourceFile
     */
    protected $sourceFile;
    
    public function __construct(SourceFile $sourceFile)
    {
        $this->sourceFile = $sourceFile;
    }
    
    /**
     * (non-PHPdoc)
     * @see sculpin\converter.IConverterContext::content()
     */
    public function content()
    {
        return $this->sourceFile->content();
    }
    
    /**
     * (non-PHPdoc)
     * @see sculpin\converter.IConverterContext::setContent()
     */
    public function setContent($content)
    {
        $this->sourceFile->setContent($content);
    }

}