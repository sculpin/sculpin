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

use sculpin\event\SourceFilesChangedEvent;

use sculpin\bundle\AbstractBundle;

class MarkdownBundle extends AbstractBundle {
    
    const CONVERTER_NAME = 'markdown';
    
    const CONFIG_ENABLED = 'markdown.enabled';
    const CONFIG_PARSERS = 'markdown.parsers';
    const CONFIG_PARSER = 'markdown.parser';
    const CONFIG_EXTENSIONS = 'markdown.extensions';

    /**
     * (non-PHPdoc)
     * @see sculpin\bundle.AbstractBundle::getBundleEvents()
     */
    static function getBundleEvents()
    {
        return array(
            Sculpin::EVENT_SOURCE_FILES_CHANGED => 'sourceFilesChanged',
        );
    }

    /**
     * (non-PHPdoc)
     * @see sculpin\bundle.AbstractBundle::configureBundle()
     */
    public function configureBundle(Sculpin $sculpin)
    {
        $configuration = $sculpin->configuration();
        $parserClass = $configuration->getConfiguration(self::CONFIG_PARSERS)->get($configuration->get(self::CONFIG_PARSER));
        $sculpin->registerConverter('markdown', new MarkdownConverter(new $parserClass));
    }
    
    /**
     * Called when Sculpin detects any source files have changed
     * @param SourceFilesChangedEvent $event
     */
    public function sourceFilesChanged(SourceFilesChangedEvent $event)
    {
        if (!$this->isEnabled($event, self::CONFIG_ENABLED)) { return; }
        $configuration = $event->configuration();
        $extensions = $configuration->get(self::CONFIG_EXTENSIONS);
        foreach ($event->inputFiles()->allFiles() as $inputFile) {
            /* @var $inputFile \sculpin\source\SourceFile */
            foreach ($extensions as $extension) {
                if (fnmatch('*.'.$extension, $inputFile->file()->getFilename())) {
                    // TODO: converters should be a const (where?)
                    $inputFile->data()->append('converters', self::CONVERTER_NAME);
                    break;
                }
            }
        }
    }

}
