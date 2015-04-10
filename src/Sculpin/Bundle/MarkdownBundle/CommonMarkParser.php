<?php

namespace Sculpin\Bundle\MarkdownBundle;

use League\CommonMark\CommonMarkConverter;
use Sculpin\Core\Converter\ParserInterface;

/**
 * Class CommonMarkParser
 */
class CommonMarkParser extends CommonMarkConverter implements ParserInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($content)
    {
        return parent::convertToHtml($content);
    }
}
