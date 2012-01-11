<?php

/*
 * This file is a part of Sculpin
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\formatter;

use sculpin\configuration\Configuration;

use sculpin\Sculpin;

class FormatContext
{
    
    /**
     * Template ID
     * @var string
     */
    protected $templateId;

    /**
     * Template
     * @var string
     */
    protected $template;
    
    /**
     * Context
     * @var Configuration
     */
    protected $context;
    
    public function __construct($templateId, $template, $context)
    {
        $this->templateId = $templateId;
        $this->template = $template;
        $this->context = new Configuration($context);
    }
    
    /**
     * Template ID
     * @return string
     */
    public function templateId()
    {
        return $this->templateId;
    }
    
    /**
     * Template
     * @return string
     */
    public function template()
    {
        return $this->template;
    }

    /**
     * Context
     * @return \sculpin\configuration\Configuration
     */
    public function context()
    {
        return $this->context;
    }

}