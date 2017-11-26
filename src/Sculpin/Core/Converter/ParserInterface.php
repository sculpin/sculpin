<?php declare(strict_types=1);

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
    public function transform(string $content): string;
}
