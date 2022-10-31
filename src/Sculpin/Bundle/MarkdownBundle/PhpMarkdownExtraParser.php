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

    /**
     * Casts $attr to String to ensure safe internal handling of preg_* calls
     *
     * @inheritDoc
     */
    public function doExtraAttributes($tag_name, $attr, $defaultIdValue = null, $classes = [])
    {
        return parent::doExtraAttributes(
            $tag_name,
            (string)$attr,
            $defaultIdValue,
            $classes
        );
    }
}
