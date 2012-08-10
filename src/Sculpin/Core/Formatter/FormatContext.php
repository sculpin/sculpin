<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Formatter;

use Dflydev\DotAccessConfiguration\Configuration;

/**
 * Formatter interface
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class FormatContext
{
    /**
     * Template ID
     *
     * @var string
     */
    protected $templateId;

    /**
     * Template
     *
     * @var string
     */
    protected $template;

    /**
     * Context
     *
     * @var Configuration
     */
    protected $context;

    /**
     * Constructor.
     *
     * @param string $templateId Template ID
     * @param string $template   Template
     * @param array  $context    Context
     */
    public function __construct($templateId, $template, $context)
    {
        $this->templateId = $templateId;
        $this->template = $template;
        $this->context = new Configuration($context);
    }

    /**
     * Template ID
     *
     * @return string
     */
    public function templateId()
    {
        return $this->templateId;
    }

    /**
     * Template
     *
     * @return string
     */
    public function template()
    {
        return $this->template;
    }

    /**
     * Context
     *
     * @return Configuration
     */
    public function context()
    {
        return $this->context;
    }

    /**
     * Formatter
     *
     * @return string
     */
    public function formatter()
    {
        return $this->context->get('formatter');
    }
}
