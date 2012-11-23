<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Source;

use Sculpin\Core\Permalink\PermalinkInterface;
use Dflydev\DotAccessConfiguration\Configuration as Data;

/**
 * Abstract Source.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
abstract class AbstractSource implements SourceInterface
{
    /**
     * Is raw?
     * @var boolean
     */
    protected $isRaw;

    /**
     * Content
     *
     * @var string
     */
    protected $content;

    /**
     * Data
     *
     * @var Data
     */
    protected $data;

    /**
     * Permalink
     *
     * @var PermalinkInterface
     */
    protected $permalink;

    /**
     * Use file reference?
     *
     * @var boolean
     */
    protected $useFileReference = false;

    /**
     * Can be formatted?
     *
     * @var boolean
     */
    protected $canBeFormatted = false;

    /**
     * Is a generator?
     *
     * @var boolean
     */
    protected $isGenerator = false;

    /**
     * Is generated?
     *
     * @var boolean
     */
    protected $isGenerated = false;

    /**
     * Initialize source
     *
     * @param bool $hasChanged Has the Source changed?
     */
    protected function init($hasChanged = null)
    {
        if (null !== $hasChanged) {
            $this->hasChanged = $hasChanged;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isRaw()
    {
        return $this->isRaw;
    }

    /**
     * {@inheritdoc}
     */
    public function content()
    {
        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content = null)
    {
        $this->content = $content;

        // If we are setting content, we are going to assume that we should
        // not be using file references on output.
        $this->useFileReference = false;
    }

    /**
     * {@inheritdoc}
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChanged()
    {
        return $this->hasChanged;
    }

    /**
     * {@inheritdoc}
     */
    public function setHasChanged()
    {
        $this->hasChanged = true;
    }

    /**
     * {@inheritdoc}
     */
    public function setHasNotChanged()
    {
        $this->hasChanged = false;
    }

    /**
     * {@inheritdoc}
     */
    public function permalink()
    {
        return $this->permalink;
    }

    /**
     * {@inheritdoc}
     */
    public function setPermalink(PermalinkInterface $permalink)
    {
        $this->permalink = $permalink;
    }

    /**
     * {@inheritdoc}
     */
    public function useFileReference()
    {
        return $this->useFileReference;
    }

    /**
     * {@inheritdoc}
     */
    public function canBeFormatted()
    {
        return $this->canBeFormatted;
    }

    /**
     * {@inheritdoc}
     */
    public function isGenerator()
    {
        return $this->isGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsGenerator()
    {
        $this->isGenerator = true;
    }

    /**
     * {@inheritdoc}
     */
    public function setisNotGenerator()
    {
        $this->isGenerator = false;
    }

    /**
     * {@inheritdoc}
     */
    public function isGenerated()
    {
        return $this->isGenerated;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsGenerated()
    {
        $this->isGenerated = true;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsNotGenerated()
    {
        $this->isGenerated = false;
    }

    /**
     * {@inheritdoc}
     */
    public function forceReprocess()
    {
        $this->init(true);
    }
}
