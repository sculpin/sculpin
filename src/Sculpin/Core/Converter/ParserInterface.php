<?php declare(strict_types=1);

namespace Sculpin\Core\Converter;

/**
 * Interface ParserInterface
 */
interface ParserInterface
{
    /**
     * @param string|mixed $content
     */
    public function transform($content): string;
}
