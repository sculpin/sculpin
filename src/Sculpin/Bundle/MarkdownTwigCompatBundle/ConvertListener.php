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

namespace Sculpin\Bundle\MarkdownTwigCompatBundle;

use Sculpin\Bundle\MarkdownBundle\SculpinMarkdownBundle;
use Sculpin\Bundle\TwigBundle\SculpinTwigBundle;
use Sculpin\Core\Event\ConvertEvent;
use Sculpin\Core\Sculpin;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Hide some twig instructions from markdown parser.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
final class ConvertListener implements EventSubscriberInterface
{
    /**
     * List of regular expressions needing placeholders
     *
     * @var array
     */
    private static $addPlaceholderRe = [
        '/^({%\s+block\s+(\w+).+?%})$/m',  // {% %} style code
        '/^({%\s+endblock\s+%})$/m',       // {% %} style code
        '/^({{.+?}})$/m',                  // {{ }} style code
    ];

    /**
     * Placeholder text
     *
     * @var string
     */
    private static $placeholder = "\n<div><!-- sculpin-hidden -->$1<!-- /sculpin-hidden --></div>\n";

    /**
     * Regex used to remove placeholder
     *
     * @var string
     */
    private static $removePlaceholderRe = "/(\n?<div><!-- sculpin-hidden -->|<!-- \/sculpin-hidden --><\/div>\n|\n?&lt;div&gt;&lt;!-- sculpin-hidden --&gt;|&lt;!-- \/sculpin-hidden --&gt;&lt;\/div&gt;\n)/m"; // @codingStandardsIgnoreLine

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Sculpin::EVENT_BEFORE_CONVERT => 'beforeConvert',
            Sculpin::EVENT_AFTER_CONVERT => 'afterConvert',
        ];
    }

    /**
     * Called before conversion
     */
    public function beforeConvert(ConvertEvent $convertEvent): void
    {
        if ($convertEvent->isHandledBy(SculpinMarkdownBundle::CONVERTER_NAME, SculpinTwigBundle::FORMATTER_NAME)) {
            $content = $convertEvent->source()->content();
            foreach (self::$addPlaceholderRe as $re) {
                $content = preg_replace($re, self::$placeholder, $content);
            }
            $content = preg_replace(
                '/{%\s+(\w+)\s+(\w+\++\w+)\s+%}(.*){%\s+end\1\s+%}/Us',
                '<div data-$1="$2">$3</div>',
                $content
            );
            $convertEvent->source()->setContent($content);
        }
    }

    /**
     * Called after conversion
     */
    public function afterConvert(ConvertEvent $convertEvent): void
    {
        if ($convertEvent->isHandledBy(SculpinMarkdownBundle::CONVERTER_NAME, SculpinTwigBundle::FORMATTER_NAME)) {
            $content = $convertEvent->source()->content();
            $content = preg_replace(self::$removePlaceholderRe, '', $content);
            $content = preg_replace(
                '/<div data-(\w+)="(\w+\++\w+)">(.*?)<\/div>/Us',
                '{% $1 \'$2\' %}$3{% end$1 %}',
                $content
            );
            $convertEvent->source()->setContent($content);
        }
    }
}
