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

use sculpin\Sculpin;

use sculpin\event\InputFilesChangedEvent;

use sculpin\bundle\IBundle;

class MarkdownBundle implements IBundle {
    
    const CONFIG_ENABLED = 'markdown.enabled';
    const CONFIG_PARSERS = 'markdown.parsers';
    const CONFIG_PARSER = 'markdown.parser';
    const CONFIG_EXTENSIONS = 'markdown.extensions';

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\EventDispatcher.EventSubscriberInterface::getSubscribedEvents()
     */
    static function getSubscribedEvents()
    {
        return array(Sculpin::EVENT_CONVERT => 'convert');
    }

    public function convert(InputFilesChangedEvent $event)
    {
        $configuration = $event->configuration();
        if (!$configuration->get(self::CONFIG_ENABLED)) { return; }
        $parserClass = $configuration->getConfiguration(self::CONFIG_PARSERS)->get($configuration->get(self::CONFIG_PARSER));
        $parser = new $parserClass;
        $extensions = $configuration->get(self::CONFIG_EXTENSIONS);
        $placeholder = "\n".'<div><!-- sculpin-hidden -->$1<!-- /sculpin-hidden --></div>'."\n";
        foreach ( array_merge($event->newFiles, $event->updatedFiles) as $inputFile ) {
            foreach ($extensions as $extension) {
                if (fnmatch('*.'.$extension, $inputFile->file()->getFilename())) {
                    $content = $inputFile->content();
                    $content = preg_replace('/^({%\s+(\w+).+?%})$/m', $placeholder, $content);
                    $content = preg_replace('/^({{.+?}})$/m', $placeholder, $content);
                    $content = $parser->transformMarkdown($content);
                    $content = preg_replace('/(<div><!-- sculpin-hidden -->|<!-- \/sculpin-hidden --><\/div>)/m', '', $content);
                    $inputFile->setContent($content);
                    break;
                }
            }
        }
    }

}