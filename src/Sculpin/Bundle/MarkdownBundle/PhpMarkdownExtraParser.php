<?php

namespace Sculpin\Bundle\MarkdownBundle;

use Michelf\MarkdownExtra;
use Sculpin\Core\Converter\ParserInterface;

/**
 * Class PhpMarkdownExtraParser
 */
class PhpMarkdownExtraParser extends MarkdownExtra implements ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($content)
    {
        return parent::transform($content);
    }
}
