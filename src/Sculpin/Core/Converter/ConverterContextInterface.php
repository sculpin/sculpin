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

/**
 * Converter Interface.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
interface ConverterContextInterface
{
    /**
     * Content
     *
     * @return string
     */
    public function content();

    /**
     * Set content
     *
     * @param string $content Content
     */
    public function setContent($content);
}
