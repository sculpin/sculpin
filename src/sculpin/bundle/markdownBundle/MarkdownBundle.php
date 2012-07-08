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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MarkdownBundle extends Bundle implements EventSubscriberInterface {
    
    const CONVERTER_NAME = 'markdown';
    
    const CONFIG_ENABLED = 'markdown.enabled';
    const CONFIG_PARSERS = 'markdown.parsers';
    const CONFIG_PARSER = 'markdown.parser';
    const CONFIG_EXTENSIONS = 'markdown.extensions';
    protected $container;

    /**
     * @{inheritdoc}
     */
    static function getSubscribedEvents()
    {
        return array(
            Sculpin::EVENT_SOURCE_SET_CHANGED => 'sourceSetChanged'
        );
    }

    public function build(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * @{inheritdoc}
     */
    public function boot()
    {
        $configuration = $this->container->get('sculpin.configuration');
        $sculpin = $this->container->get('sculpin');
        $parserClass = $configuration->getConfiguration(self::CONFIG_PARSERS)->get($configuration->get(self::CONFIG_PARSER));
        $sculpin->registerConverter('markdown', new MarkdownConverter(new $parserClass));
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
