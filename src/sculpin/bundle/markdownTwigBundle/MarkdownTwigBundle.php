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

use sculpin\bundle\AbstractBundle;
use sculpin\bundle\markdownBundle\MarkdownBundle;
use sculpin\bundle\twigBundle\TwigBundle;
use sculpin\event\ConvertSourceEvent;
use sculpin\Sculpin;

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
class MarkdownTwigBundle extends AbstractBundle
{
    /**
     * List of regular expresses needing placeholders
     *
     * @var array
     */
    protected static $addPlaceholderRe = array(
        '/^({%\s+(\w+).+?%})$/m',  // {% %} style code
        '/^({{.+?}})$/m',          // {{ }} style code
    );

    /**
     * Placeholder text
     *
     * @var string
     */
    protected static $placeholder = "\n<div><!-- sculpin-hidden -->$1<!-- /sculpin-hidden --></div>\n";

    /**
     * Regex used to remove placeholder
     *
     * @var unknown_type
     */
    protected static $removePlaceholderRe = '/(<div><!-- sculpin-hidden -->|<!-- \/sculpin-hidden --><\/div>)/m';

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Sculpin::EVENT_BEFORE_CONVERT => 'beforeConvert',
            Sculpin::EVENT_AFTER_CONVERT => 'afterConvert',
        );
    }

    /**
     * Called before conversion
     *
     * @param ConvertSourceEvent $convertSourceEvent Convert source Event
     */
    public function beforeConvert(ConvertSourceEvent $convertSourceEvent)
    {
        if ($convertSourceEvent->isHandledBy(MarkdownBundle::CONVERTER_NAME, TwigBundle::FORMATTER_NAME)) {
            $content = $convertSourceEvent->source()->content();
            foreach (self::$addPlaceholderRe as $re) {
                $content = preg_replace($re, self::$placeholder, $content);
            }
            $convertSourceEvent->source()->setContent($content);
        }
    }

    /**
     * Called after conversion
     *
     * @param ConvertSourceEvent $convertSourceEvent Convert source event
     */
    public function afterConvert(ConvertSourceEvent $convertSourceEvent)
    {
        if ($convertSourceEvent->isHandledBy(MarkdownBundle::CONVERTER_NAME, TwigBundle::FORMATTER_NAME)) {
            $content = $convertSourceEvent->source()->content();
            $content = preg_replace(self::$removePlaceholderRe, '', $content);
            $convertSourceEvent->source()->setContent($content);
        }
    }
}
