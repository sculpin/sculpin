<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Util;

/**
 * Directory Separator Normalizer
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class DirectorySeparatorNormalizer
{
    /**
     * Constructor
     *
     * @param string $preferredDirectorySeparator Preferred directory separator
     */
    public function __construct($preferredDirectorySeparator = '/')
    {
        $this->preferredDirectorySeparator = $preferredDirectorySeparator;
        $this->directorySeparator = DIRECTORY_SEPARATOR;
    }

    /**
     * Set directory separator
     *
     * Useful for testing to override DIRECTORY_SEPARATOR.
     *
     * @param string $directorySeparator Directory separator
     *
     * @return DirectorySeparatorNormalizer
     */
    public function setDirectorySeparator($directorySeparator)
    {
        $this->directorySeparator = $directorySeparator;

        return $this;
    }

    /**
     * Normalize filesystem paths to a preferred $separator
     *
     * @param string $path Path
     *
     * @return null|string
     */
    public function normalize($path = null)
    {
        if ($this->preferredDirectorySeparator === $this->directorySeparator) {
            return $path;
        }

        if (null === $path) {
            return $path;
        }

        return implode($this->preferredDirectorySeparator, explode($this->directorySeparator, $path));
    }
}
