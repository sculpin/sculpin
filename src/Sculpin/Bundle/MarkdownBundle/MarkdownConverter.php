<?php

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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Markdown Converter.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class MarkdownConverter implements ConverterInterface, EventSubscriberInterface
{
    /**
     * Markdown
     *
     * @var ParserInterface
     */
    protected $markdown;

    /**
     * Extensions
     *
     * @var array
     */
    protected $extensions = array();

    /**
     * Constructor.
     *
     * @param ParserInterface $markdown
     * @param array           $extensions Extensions
     */
    public function __construct(ParserInterface $markdown, array $extensions = array())
    {
        $this->markdown = $markdown;
        $this->markdown->header_id_func = array($this, 'generateHeaderId');
        $this->extensions = $extensions;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(ConverterContextInterface $converterContext)
    {
        $converterContext->setContent($this->markdown->transform($converterContext->content()));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Sculpin::EVENT_BEFORE_RUN => 'beforeRun',
        );
    }

    /**
     * Before run
     *
     * @param SourceSetEvent $sourceSetEvent Source Set Event
     */
    public function beforeRun(SourceSetEvent $sourceSetEvent)
    {
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
     * @param string $headerText raw markdown input for the header name
     * @return string
     */
    public function generateHeaderId($headerText)
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
        $map = array(
            ' ' => '-',
            '(' => '',
            ')' => '',
            '[' => '',
            ']' => '',
        );
        return rawurlencode(strtolower(
            strtr($result, $map)
        ));
    }
}
