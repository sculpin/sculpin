<?php declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Formatter;

use Dflydev\DotAccessConfiguration\Configuration as Data;

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
     * Data
     *
     * @var Data
     */
    protected $data;

    /**
     * Constructor.
     *
     * @param string $templateId Template ID
     * @param string $template   Template
     * @param array  $data       Data
     */
    public function __construct(string $templateId, string $template, array $data)
    {
        $this->templateId = $templateId;
        $this->template = $template;
        $this->data = new Data($data);
    }

    /**
     * Template ID
     */
    public function templateId(): string
    {
        return $this->templateId;
    }

    /**
     * Template
     */
    public function template(): string
    {
        return $this->template;
    }

    /**
     * Data
     */
    public function data(): Data
    {
        return $this->data;
    }

    /**
     * Formatter
     */
    public function formatter(): string
    {
        return $this->data->get('formatter');
    }
}
