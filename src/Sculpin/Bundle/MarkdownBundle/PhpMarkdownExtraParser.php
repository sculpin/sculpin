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
    #[\Override]
    public function transform($content): string
    {
        return parent::transform($content);
    }

    /**
     * Casts $attr to String to ensure safe internal handling of preg_* calls
     *
     * @inheritDoc
     */
    #[\Override]
    public function doExtraAttributes($tag_name, $attr, $defaultIdValue = null, $classes = []): string
    {
        return parent::doExtraAttributes(
            $tag_name,
            (string)$attr,
            $defaultIdValue,
            $classes
        );
    }
}
