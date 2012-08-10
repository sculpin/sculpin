<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core;

use Dflydev\DotAccessConfiguration\Configuration as Data;
use Sculpin\Core\Configuration\Configuration;
use Sculpin\Core\Converter\ConverterInterface;
use Sculpin\Core\Converter\SourceConverterContext;
use Sculpin\Core\Event\ConvertEvent;
use Sculpin\Core\Event\FormatEvent;
use Sculpin\Core\Event\SourceSetEvent;
use Sculpin\Core\Formatter\FormatContext;
use Sculpin\Core\Formatter\FormatterInterface;
use Sculpin\Core\Permalink\SourcePermalinkFactory;
use Sculpin\Core\Source\DataSourceInterface;
use Sculpin\Core\Source\SourceInterface;
use Sculpin\Core\Source\SourceSet;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Sculpin.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class Sculpin
{
    const EVENT_BEFORE_RUN = 'sculpin.core.before_run';
    const EVENT_BEFORE_RUN_AGAIN = 'sculpin.core.before_run_again';
    const EVENT_AFTER_RUN = 'sculpin.core.after_run';

    const EVENT_BEFORE_CONVERT = 'sculpin.core.before_convert';
    const EVENT_AFTER_CONVERT = 'sculpin.core.after_convert';

    const EVENT_BEFORE_FORMAT = 'sculpin.core.before_format';
    const EVENT_AFTER_FORMAT = 'sculpin.core.after_format';

    /**
     * Configuration
     *
     * @var Configuration
     */
    protected $configuration;

    /**
     * Permalink factory
     *
     * @var SourcePermalinkFactory
     */
    protected $permalinkFactory;

    /**
     * Converters
     *
     * @var array
     */
    protected $converters = array();

    /**
     * Formatters
     *
     * @var array
     */
    protected $formatters = array();

    /**
     * Constructor.
     *
     * @param Configuration          $configuration    Configuration
     * @param EventDispatcher        $eventDispatcher  Event dispatcher
     * @param SourcePermalinkFactory $permalinkFactory Permalink factory
     */
    public function __construct(Configuration $configuration, EventDispatcher $eventDispatcher, SourcePermalinkFactory $permalinkFactory)
    {
        $this->configuration = $configuration;
        $this->eventDispatcher = $eventDispatcher;
        $this->permalinkFactory = $permalinkFactory;
    }

    /**
     * Run.
     *
     * @param DataSourceInterface $dataSource Data source
     * @param SourceSet           $sourceSet  Source set
     */
    public function run(DataSourceInterface $dataSource, SourceSet $sourceSet)
    {
        $dataSource->refresh($sourceSet);

        $this->eventDispatcher->dispatch(self::EVENT_BEFORE_RUN, new SourceSetEvent($sourceSet));

        // TODO: Find a better name for this event.
        $this->eventDispatcher->dispatch(self::EVENT_BEFORE_RUN_AGAIN, new SourceSetEvent($sourceSet));

        foreach ($sourceSet->updatedSources() as $source) {
            $permalink = $this->permalinkFactory->create($source);
            $source->setPermalink($permalink);
            $this->convertSource($source);
            $originalContent = $source->content();
            if ($source->canBeFormatted()) {
                $source->setContent($this->formatSourcePage($source));
            }
        }

        $this->eventDispatcher->dispatch(self::EVENT_AFTER_RUN, new SourceSetEvent($sourceSet));
    }

    /**
     * Register converter
     *
     * @param string             $name      Name
     * @param ConverterInterface $converter Converter
     */
    public function registerConverter($name, ConverterInterface $converter)
    {
        $this->converters[$name] = $converter;
    }

    /**
     * Converter
     *
     * @param string $name Name
     *
     * @return ConverterInterface
     */
    public function converter($name)
    {
        return $this->converters[$name];
    }

    /**
     * Convert Source
     *
     * @param SourceInterface $source Source
     */
    public function convertSource(SourceInterface $source)
    {
        $converters = $source->data()->get('converters');
        if (!$converters || !is_array($converters)) {
            return;
        }

        foreach ($converters as $converter) {
            $this->eventDispatcher->dispatch(self::EVENT_BEFORE_CONVERT, new ConvertEvent($source, $converter, $this->configuration->defaultFormatter()));
            $this->converter($converter)->convert(new SourceConverterContext($source));
            $this->eventDispatcher->dispatch(self::EVENT_AFTER_CONVERT, new ConvertEvent($source, $converter, $this->configuration->defaultFormatter()));
        }
    }

    protected function buildBaseFormatContext($context)
    {
        $baseContext = new Data(array(
            'site' => $this->configuration->export(),
            'page' => $context,
            'formatter' => $this->configuration->defaultFormatter(),
            'converters' => array(),
        ));

        return $baseContext;
    }
    /**
     * Build a Format Context
     *
     * @param string $templateId Template ID
     * @param string $template   Template
     * @param array  $context    Context
     *
     * @return FormatContext
     */
    public function buildFormatContext($templateId, $template, $context)
    {
        $baseContext = $this->buildBaseFormatContext($context);

        foreach (array('layout', 'formatter', 'converters') as $key) {
            if (isset($context[$key])) {
                $baseContext->set($key, $context[$key]);
            }
        }

        return new FormatContext($templateId, $template, $baseContext->export());
    }

    /**
     * Register formatter
     *
     * @param string             $name      Name
     * @param FormatterInterface $formatter Formatter
     */
    public function registerFormatter($name, FormatterInterface $formatter)
    {
        $this->formatters[$name] = $formatter;

        $this->configuration->setDefaultFormatter($name);
    }

    /**
     * Formatter
     *
     * @param string $name Name
     *
     * @return FormatterInterface
     */
    public function formatter($name)
    {
        return $this->formatters[$name];
    }

    /**
     * Format a page
     *
     * @param string $templateId Template ID
     * @param string $template   Template
     * @param array  $context    Context
     *
     * @return string
     */
    public function formatPage($templateId, $template, $context)
    {
        $formatContext = $this->buildFormatContext($templateId, $template, $context);

        if (!$formatContext->formatter()) {
            return $template;
        }

        $this->eventDispatcher->dispatch(self::EVENT_BEFORE_FORMAT, new FormatEvent($formatContext));
        $response = $this->formatter($formatContext->formatter())->formatPage($formatContext);
        $this->eventDispatcher->dispatch(self::EVENT_AFTER_FORMAT, new FormatEvent($formatContext));

        return $response;
    }

    /**
     * Format a page for a Source
     *
     * @param SourceInterface $source Source
     *
     * @return string
     */
    public function formatSourcePage(SourceInterface $source)
    {
        return $this->formatPage(
            $source->sourceId(),
            $source->content(),
            $source->data()->export()
        );
    }

    /**
     * Format blocks
     *
     * @param string $templateId Template ID
     * @param string $template   Template
     * @param array  $context    Context
     *
     * @return array
     */
    public function formatBlocks($templateId, $template, $context)
    {
        $formatContext = $this->buildFormatContext($templateId, $template, $context);

        if (!$formatContext->formatter()) {
            return array('content' => $template);
        }

        $this->eventDispatcher->dispatch(self::EVENT_BEFORE_FORMAT, new FormatEvent($formatContext));
        $response = $this->formatter($formatContext->formatter())->formatBlocks($formatContext);
        $this->eventDispatcher->dispatch(self::EVENT_AFTER_FORMAT, new FormatEvent($formatContext));

        return $response;
    }

    /**
     * Format blocks for a Source
     *
     * @param SourceInterface $source Source
     *
     * @return array
     */
    public function formatSourceBlocks(SourceInterface $source)
    {
        return $this->formatBlocks(
            $source->sourceId(),
            $source->content(),
            $source->data()->export()
        );
    }
}
