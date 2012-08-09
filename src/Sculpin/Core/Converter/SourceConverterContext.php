<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Converter;

use Sculpin\Core\Source\SourceInterface;

/**
 * Converter Interface.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class SourceConverterContext implements ConverterContextInterface
{
    /**
     * Source
     *
     * @var SourceInterface
     */
    protected $source;

    /**
     * Constructor
     *
     * @param SourceInterface $source Source
     */
    public function __construct(SourceInterface $source)
    {
        $this->source = $source;
    }

    /**
     * {@inheritdoc}
     */
    public function content()
    {
        return $this->source->content();
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        $this->source->setContent($content);
    }
}
