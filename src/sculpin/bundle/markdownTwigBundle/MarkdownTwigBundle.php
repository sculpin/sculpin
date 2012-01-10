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

use sculpin\bundle\twigBundle\TwigBundle;

use sculpin\event\ConvertSourceFileEvent;

use sculpin\bundle\markdownBundle\MarkdownBundle;

use sculpin\Sculpin;

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
    public function beforeConvert(ConvertSourceFileEvent $event)
    {
        if ($event->converter() == MarkdownBundle::CONVERTER_NAME and $event->sculpin()->deriveSourceFileFormatter($event->sourceFile()) == TwigBundle::FORMATTER_NAME) {
            $content = $event->sourceFile()->content();
            $content = preg_replace('/^({%\s+(\w+).+?%})$/m', self::$PLACEHOLDER, $content);
            $content = preg_replace('/^({{.+?}})$/m', self::$PLACEHOLDER, $content);
            $event->sourceFile()->setContent($content);
        }
    }

    /**
     * Called after conversion
     * @param SourceFilesChangedEvent $event
     */
    public function afterConvert(ConvertSourceFileEvent $event)
    {
        if ($event->converter() == MarkdownBundle::CONVERTER_NAME and $event->sculpin()->deriveSourceFileFormatter($event->sourceFile()) == TwigBundle::FORMATTER_NAME) {
            $content = $event->sourceFile()->content();
            $content = preg_replace(self::$PLACEHOLDER_RE, '', $content);
            $event->sourceFile()->setContent($content);
        }
    }

}
