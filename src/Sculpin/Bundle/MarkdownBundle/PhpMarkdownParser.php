<?php

declare(strict_types=1);

namespace Sculpin\Bundle\MarkdownBundle;

use Michelf\Markdown;
use Sculpin\Core\Converter\ParserInterface;

/**
 * Provide Michelf\Markdown as Sculpin parser.
 */
class PhpMarkdownParser extends Markdown implements ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($content): string
    {
        return parent::transform($content);
    }
}
