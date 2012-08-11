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

use dflydev\markdown\IMarkdownParser;
use Sculpin\Core\Converter\ConverterContextInterface;
use Sculpin\Core\Converter\ConverterInterface;
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
     * Markdown parser
     *
     * @var IMarkdownParser
     */
    protected $markdownParser;

    /**
     * Extensions
     *
     * @var array
     */
    protected $extensions = array();

    /**
     * Constructor.
     *
     * @param IMarkdownParser $markdownParser Markdown parser
     * @param array           $extensions     Extensions
     */
    public function __construct(IMarkdownParser $markdownParser, array $extensions = array())
    {
        $this->markdownParser = $markdownParser;
        $this->extensions = $extensions;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(ConverterContextInterface $converterContext)
    {
        $converterContext->setContent($this->markdownParser->transformMarkdown($converterContext->content()));
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
}
