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

namespace Sculpin\Bundle\TextileBundle;

use Netcarver\Textile\Parser;
use Sculpin\Core\Converter\ConverterContextInterface;
use Sculpin\Core\Converter\ConverterInterface;
use Sculpin\Core\Event\SourceSetEvent;
use Sculpin\Core\Sculpin;
use Sculpin\Core\Source\SourceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
final class TextileConverter implements ConverterInterface, EventSubscriberInterface
{
    /**
     * @var Parser
     */
    private $textileParser;

    /**
     * File name extensions that are handled as textile.
     *
     * @var string[]
     */
    private $extensions = [];

    /**
     * @param string[] $extensions file name extensions that are handled as markdown
     */
    public function __construct(Parser $parser, array $extensions = [])
    {
        $this->textileParser = $parser;
        $this->extensions = $extensions;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(ConverterContextInterface $converterContext): void
    {
        $converterContext->setContent($this->textileParser->parse($converterContext->content()));
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
                    $source->data()->append('converters', SculpinTextileBundle::CONVERTER_NAME);
                    break;
                }
            }
        }
    }
}
