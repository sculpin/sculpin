<?php

declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\MarkdownBundle;

use Sculpin\Core\Converter\ConverterContextInterface;
use Sculpin\Core\Converter\ConverterInterface;
use Sculpin\Core\Converter\ParserInterface;
use Sculpin\Core\Event\SourceSetEvent;
use Sculpin\Core\Sculpin;
use Sculpin\Core\Source\SourceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Michelf\Markdown;

/**
 * Convert Markdown content with michelf/php-markdown.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
final class MarkdownConverter implements ConverterInterface, EventSubscriberInterface
{
    /**
     * @var ParserInterface
     */
    private $markdown;

    /**
     * File name extensions that are handled as markdown.
     *
     * @var string[]
     */
    private $extensions = [];

    /**
     * Constructor.
     *
     * @param ParserInterface $markdown
     * @param string[]        $extensions file name extensions that are handled as markdown
     */
    public function __construct(ParserInterface $markdown, array $extensions = [])
    {
        $this->markdown = $markdown;
        if ($this->markdown instanceof Markdown) {
            $this->markdown->header_id_func = [$this, 'generateHeaderId'];
        }
        $this->extensions = $extensions;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(ConverterContextInterface $converterContext): void
    {
        $converterContext->setContent($this->markdown->transform($converterContext->content()));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Sculpin::EVENT_BEFORE_RUN => 'beforeRun',
        ];
    }

    /**
     * Event hook to register this converter for all sources that have markdown file extensions.
     *
     * @internal
     */
    public function beforeRun(SourceSetEvent $sourceSetEvent): void
    {
        /** @var SourceInterface $source */
        foreach ($sourceSetEvent->updatedSources() as $source) {
            foreach ($this->extensions as $extension) {
                if (fnmatch("*.{$extension}", $source->filename())) {
                    $source->data()->append('converters', SculpinMarkdownBundle::CONVERTER_NAME);
                    break;
                }
            }
        }
    }


    /**
     * This method is called to generate an id="" attribute for a header.
     *
     * @internal
     *
     * @param string $headerText raw markdown input for the header name
     */
    public function generateHeaderId(string $headerText): string
    {

        // $headerText is completely raw markdown input. We need to strip it
        // from all markup, because we are only interested in the actual 'text'
        // part of it.

        // Step 1: Remove html tags.
        $result = strip_tags($headerText);

        // Step 2: Remove all markdown links. To do this, we simply remove
        // everything between ( and ) if the ( occurs right after a ].
        $result = preg_replace('%
            (?<= \\]) # Look behind to find ]
            (
                \\(     # match (
                [^\\)]* # match everything except )
                \\)     # match )
            )

            %x', '', $result);

        // Step 3: Convert spaces to dashes, and remove unwanted special
        // characters.
        $map = [
            ' ' => '-',
            '(' => '',
            ')' => '',
            '[' => '',
            ']' => '',
        ];
        return rawurlencode(strtolower(
            strtr($result, $map)
        ));
    }
}
