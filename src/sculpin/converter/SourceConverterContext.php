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

use sculpin\source\ISource;

class SourceConverterContext implements IConverterContext
{

    /**
     * Source
     * 
     * @var ISource
     */
    protected $source;
    
    /**
     * Constructor
     * 
     * @param ISource $source
     */
    public function __construct(ISource $source)
    {
        $this->source = $source;
    }

    /**
     * @{inheritdoc}
     */
    public function content()
    {
        return $this->source->content();
    }

    /**
     * @{inheritdoc}
     */
    public function setContent($content)
    {
        $this->source->setContent($content);
    }
}