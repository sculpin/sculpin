<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\bundle\markdownBundle;

use sculpin\bundle\AbstractBundle;
use sculpin\event\SourceSetEvent;
use sculpin\Sculpin;

/**
 * Markdown Bundle
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class MarkdownBundle extends AbstractBundle
{
    const CONVERTER_NAME = 'markdown';

    const CONFIG_ENABLED = 'markdown.enabled';
    const CONFIG_PARSERS = 'markdown.parsers';
    const CONFIG_PARSER = 'markdown.parser';
    const CONFIG_EXTENSIONS = 'markdown.extensions';

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Sculpin::EVENT_SOURCE_SET_CHANGED => 'sourceSetChanged'
        );
    }

    /**
     * @{inheritdoc}
     */
    public function boot()
    {
        $parser = $this->configuration->get(self::CONFIG_PARSER);
        $parserClass = $this->configuration->getConfiguration(self::CONFIG_PARSERS)->get($parser);
        $this->sculpin->registerConverter('markdown', new MarkdownConverter(new $parserClass));
    }

    /**
     * Called when Sculpin detects source set has changed sources
     *
     * @param SourceSetEvent $sourceSetEvent
     */
    public function sourceSetChanged(SourceSetEvent $sourceSetEvent)
    {
        if (!$this->configuration->get(self::CONFIG_ENABLED)) {
            return;
        }

        $extensions = $this->configuration->get(self::CONFIG_EXTENSIONS);

        foreach ($sourceSetEvent->updatedSources() as $source) {
            foreach ($extensions as $extension) {
                if (fnmatch("*.{$extension}", $source->filename())) {
                    // TODO: converters should be a const (where?)
                    $source->data()->append('converters', self::CONVERTER_NAME);
                    break;
                }
            }
        }
    }
}
