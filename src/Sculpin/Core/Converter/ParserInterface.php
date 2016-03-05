<?php

namespace Sculpin\Core\Converter;

/**
 * Interface ParserInterface
 */
interface ParserInterface
{
    /**
     * @param string $content
     *
     * @return string
     */
    public function transform($content);
}
