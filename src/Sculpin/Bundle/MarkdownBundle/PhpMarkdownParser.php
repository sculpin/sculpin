<?php

namespace Sculpin\Bundle\MarkdownBundle;

use Michelf\Markdown;
use Sculpin\Core\Converter\ParserInterface;

/**
 * Class PhpMarkdownParser
 */
class PhpMarkdownParser extends Markdown implements ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($content)
    {
        return parent::transform($content);
    }
}
