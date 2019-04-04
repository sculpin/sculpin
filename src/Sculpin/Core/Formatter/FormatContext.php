<?php

declare(strict_types=1);

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
 * @author Beau Simensen <beau@dflydev.com>
 */
final class FormatContext
{
    /**
     * @var string
     */
    private $templateId;

    /**
     * @var string
     */
    private $template;

    /**
     * @var Data
     */
    private $data;

    public function __construct(string $templateId, string $template, array $data)
    {
        $this->templateId = $templateId;
        $this->template = $template;
        $this->data = new Data($data);
    }

    public function templateId(): string
    {
        return $this->templateId;
    }

    public function template(): string
    {
        return $this->template;
    }

    public function data(): Data
    {
        return $this->data;
    }

    public function formatter(): ?string
    {
        return $this->data->get('formatter');
    }
}
