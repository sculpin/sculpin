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
     * Constructor.
     *
     * @param IMarkdownParser $markdownParser Markdown parser
     */
    public function __construct(IMarkdownParser $markdownParser)
    {
        $this->markdownParser = $markdownParser;
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
            Sculpin::EVENT_BEFORE_RUN => 'beforeRun'
        );
    }

    /**
     * Before run
     *
     * @param SourceSetEvent $sourceSetEvent Source Set Event
     */
    public function beforeRun(SourceSetEvent $sourceSetEvent)
    {
        $extensions = array('md', 'markdown');
        foreach ($sourceSetEvent->updatedSources() as $source) {
            foreach ($extensions as $extension) {
                if (fnmatch("*.{$extension}", $source->filename())) {
                    $source->data()->append('converters', SculpinMarkdownBundle::CONVERTER_NAME);
                    break;
                }
            }
        }
    }
}
