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

/**
 * Support for combining Markdown converstion with Twig formatting
 * 
 * Markdown will wrap <p></p> around things like {% block content %}
 * or {{ post.blocks.content|raw }} which is most likely not the
 * desired behaviour.
 * 
 * This bundle attaches to before and after convert events and wraps
 * these Twig elements in a block level element (<div></div>) which
 * should cause well behaved Markdown parsers to not wrap these elements.
 * 
 * @author Beau Simensen <beau@dflydev.com>
 */
class MarkdownTwigBundle extends AbstractBundle {

    /**
     * List of regular expresses needing placeholders
     * @var array
     */
    protected static $ADD_PLACEHOLDER_RES = array(
        '/^({%\s+(\w+).+?%})$/m',  // {% %} style code
        '/^({{.+?}})$/m',          // {{ }} style code
    );

    /**
     * Placeholder text
     * @var string
     */
    protected static $PLACEHOLDER = "\n<div><!-- sculpin-hidden -->$1<!-- /sculpin-hidden --></div>\n";

    /**
     * Regex used to remove placeholder
     * @var unknown_type
     */
    protected static $REMOVE_PLACEHOLDER_RE = '/(<div><!-- sculpin-hidden -->|<!-- \/sculpin-hidden --><\/div>)/m';

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
        if ($event->isHandledBy(MarkdownBundle::CONVERTER_NAME, TwigBundle::FORMATTER_NAME)) {
            $content = $event->sourceFile()->content();
            foreach (self::$ADD_PLACEHOLDER_RES as $re) {
                $content = preg_replace($re, self::$PLACEHOLDER, $content);
            }
            $event->sourceFile()->setContent($content);
        }
    }

    /**
     * Called after conversion
     * @param SourceFilesChangedEvent $event
     */
    public function afterConvert(ConvertSourceFileEvent $event)
    {
        if ($event->isHandledBy(MarkdownBundle::CONVERTER_NAME, TwigBundle::FORMATTER_NAME)) {
            $content = $event->sourceFile()->content();
            $content = preg_replace(self::$REMOVE_PLACEHOLDER_RE, '', $content);
            $event->sourceFile()->setContent($content);
        }
    }

}
