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

use sculpin\event\SourceSetEvent;
use sculpin\Sculpin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MarkdownBundle extends Bundle implements EventSubscriberInterface {
    
    const CONVERTER_NAME = 'markdown';
    
    const CONFIG_ENABLED = 'markdown.enabled';
    const CONFIG_PARSERS = 'markdown.parsers';
    const CONFIG_PARSER = 'markdown.parser';
    const CONFIG_EXTENSIONS = 'markdown.extensions';

    protected $configuration;

    /**
     * The Sculpin object.
     *
     * @var Sculpin
     */
    protected $sculpin;

    /**
     * @{inheritdoc}
     */
    static function getSubscribedEvents()
    {
        return array(
            Sculpin::EVENT_SOURCE_SET_CHANGED => 'sourceSetChanged'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        // Extract objects that are required from the container.
        $this->configuration = $container->get('sculpin.configuration');
        $this->sculpin = $container->get('sculpin');
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
        $configuration = $sourceSetEvent->configuration();
        if (!$configuration->get(self::CONFIG_ENABLED)) {
            return;
        }

        $extensions = $configuration->get(self::CONFIG_EXTENSIONS);

        foreach ($sourceSetEvent->updatedSources() as $source) {
            /* @var $source \sculpin\source\ISource */
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
