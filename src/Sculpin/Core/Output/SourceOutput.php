<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Output;

use Sculpin\Core\Source\SourceInterface;

/**
 * Source Output.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class SourceOutput implements OutputInterface
{
    /**
     * Source
     *
     * @var SourceInterface
     */
    protected $source;

    /**
     * Constructor.
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
    public function outputId()
    {
        return $this->source->sourceId();
    }

    /**
     * {@inheritdoc}
     */
    public function pathname()
    {
        return $this->source->relativePathname();
    }

    /**
     * {@inheritdoc}
     */
    public function permalink()
    {
        return $this->source->permalink();
    }

    /**
     * {@inheritdoc}
     */
    public function hasFileReference()
    {
        return $this->source->useFileReference();
    }

    /**
     * {@inheritdoc}
     */
    public function file()
    {
        return $this->source->useFileReference() ? $this->source->file() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function formattedContent()
    {
        return $this->source->useFileReference()? null : $this->source->formattedContent();
    }
}
