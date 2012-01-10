<?php

/*
 * This file is a part of Sculpin.
 * 
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\bundle\markdownTwigBundle;

use sculpin\Sculpin;

use sculpin\event\SourceFilesChangedEvent;

use sculpin\bundle\AbstractBundle;

class MarkdownTwigBundle extends AbstractBundle {
    
    protected static $PLACEHOLDER = "\n<div><!-- sculpin-hidden -->$1<!-- /sculpin-hidden --></div>\n";
    protected static $PLACEHOLDER_RE = '/(<div><!-- sculpin-hidden -->|<!-- \/sculpin-hidden --><\/div>)/m';

    /**
     * (non-PHPdoc)
     * @see sculpin\bundle.AbstractBundle::getBundleEvents()
     */
    static function getBundleEvents()
    {
        return array(
            Sculpin::EVENT_BEFORE_CONVERT => 'beforeConvert',
            Sculpin::EVENT_AFTER_CONVERT => 'afterConvert',
        );
    }
    
    /**
     * Called before conversion
     * @param SourceFilesChangedEvent $event
     */
    public function beforeConvert(SourceFilesChangedEvent $event)
    {
        foreach ( $event->inputFiles()->changedFiles() as $inputFile ) {
            /* @var $inputFile \sculpin\source\SourceFile */
            if ($converters = $inputFile->data()->get('converters') and is_array($converters) and in_array('markdown', $converters)) {
                // TODO: converters should be a const
                $content = $inputFile->content();
                $content = preg_replace('/^({%\s+(\w+).+?%})$/m', self::$PLACEHOLDER, $content);
                $content = preg_replace('/^({{.+?}})$/m', self::$PLACEHOLDER, $content);
                $inputFile->setContent($content);
            }
        }
        //
    }

    /**
     * Called after conversion
     * @param SourceFilesChangedEvent $event
     */
    public function afterConvert(SourceFilesChangedEvent $event)
    {
        foreach ( $event->inputFiles()->changedFiles() as $inputFile ) {
            /* @var $inputFile \sculpin\source\SourceFile */
            if ($converters = $inputFile->data()->get('converters') and is_array($converters) and in_array('markdown', $converters)) {
                // TODO: converters should be a const
                $content = $inputFile->content();
                $content = preg_replace(self::$PLACEHOLDER_RE, '', $content);
                $inputFile->setContent($content);
            }
        }
    }
    

}
