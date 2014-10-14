<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Permalink;

/**
 * Permalink.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class Permalink implements PermalinkInterface
{
    /**
     * Relative file path
     *
     * @var string
     */
    private $relativeFilePath;

    /**
     * Relative URL path
     *
     * @var string
     */
    private $relativeUrlPath;

    /**
     * Constructor.
     *
     * @param string $relativeFilePath Relative file path
     * @param string $relativeUrlPath  Relative URL path
     */
    public function __construct($relativeFilePath, $relativeUrlPath)
    {
        $this->relativeFilePath = $relativeFilePath;
        $this->relativeUrlPath = $relativeUrlPath;
    }

    /**
     * {@inheritdoc}
     */
    public function relativeFilePath()
    {
        return $this->relativeFilePath;
    }

    /**
     * {@inheritdoc}
     */
    public function relativeUrlPath()
    {
        return $this->relativeUrlPath;
    }
}
