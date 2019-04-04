<?php

declare(strict_types=1);

namespace Sculpin\Bundle\MarkdownBundle;

use Michelf\MarkdownExtra;
use Sculpin\Core\Converter\ParserInterface;

/**
 * Provide Michelf\MarkdownExtra as Sculpin parser.
 */
class PhpMarkdownExtraParser extends MarkdownExtra implements ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($content): string
    {
        return parent::transform($content);
    }
}
