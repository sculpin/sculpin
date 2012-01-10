<?php

/*
 * This file is a part of Sculpin.
 * 
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\bundle\markdownBundle;


use sculpin\Sculpin;

use sculpin\converter\IConverterContext;

use dflydev\markdown\IMarkdownParser;

use sculpin\converter\IConverter;

class MarkdownConverter implements IConverter
{
    
    /**
     * Markdown parser
     * @var IMarkdownParser
     */
    protected $markdownParser;
    
    public function __construct(IMarkdownParser $markdownParser)
    {
        $this->markdownParser = $markdownParser;
    }
    
    /**
     * (non-PHPdoc)
     * @see sculpin\converter.IConverter::convert()
     */
    public function convert(Sculpin $sculpin, IConverterContext $converterContext) 
    {
        $converterContext->setContent($this->markdownParser->transformMarkdown($converterContext->content()));
    }
}
