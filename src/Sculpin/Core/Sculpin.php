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

use Dflydev\DotAccessConfiguration\Configuration;
use Sculpin\Core\Converter\ConverterManager;
use Sculpin\Core\Event\SourceSetEvent;
use Sculpin\Core\Formatter\FormatterManager;
use Sculpin\Core\Generator\GeneratorManager;
use Sculpin\Core\Output\SourceOutput;
use Sculpin\Core\Output\WriterInterface;
use Sculpin\Core\Permalink\SourcePermalinkFactory;
use Sculpin\Core\Source\DataSourceInterface;
use Sculpin\Core\Source\SourceSet;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Sculpin.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class Sculpin
{
    const VERSION = '@package_version@';

    const EVENT_BEFORE_RUN = 'sculpin.core.before_run';
    const EVENT_BEFORE_RUN_AGAIN = 'sculpin.core.before_run_again';
    const EVENT_AFTER_RUN = 'sculpin.core.after_run';

    const EVENT_BEFORE_CONVERT = 'sculpin.core.before_convert';
    const EVENT_AFTER_CONVERT = 'sculpin.core.after_convert';

    const EVENT_BEFORE_FORMAT = 'sculpin.core.before_format';
    const EVENT_AFTER_FORMAT = 'sculpin.core.after_format';

    /**
     * Site Configuration
     *
     * @var Configuration
     */
    protected $siteConfiguration;

    /**
     * Event Dispatcher
     *
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * Permalink factory
     *
     * @var SourcePermalinkFactory
     */
    protected $permalinkFactory;

    /**
     * Writer
     *
     * @var WriterInterface
     */
    protected $writer;

    /**
     * Generator Manager
     *
     * @var GeneratorManager
     */
    protected $generatorManager;

    /**
     * Formatter Manager
     *
     * @var FormatterManager
     */
    protected $formatterManager;

    /**
     * Converter Manager
     *
     * @var ConverterManager
     */
    protected $converterManager;

    /**
     * Constructor.
     *
     * @param Configuration          $siteConfiguration Site Configuration
     * @param EventDispatcher        $eventDispatcher   Event dispatcher
     * @param SourcePermalinkFactory $permalinkFactory  Permalink factory
     * @param WriterInterface        $writer            Writer
     * @param GeneratorManager       $generatorManager  Generator Manager
     * @param FormatterManager       $formatterManager  Formatter Manager
     * @param ConverterManager       $converterManager  Converter Manager
     */
    public function __construct(Configuration $siteConfiguration, EventDispatcher $eventDispatcher, SourcePermalinkFactory $permalinkFactory, WriterInterface $writer, GeneratorManager $generatorManager, FormatterManager $formatterManager, ConverterManager $converterManager)
    {
        $this->siteConfiguration = $siteConfiguration;
        $this->eventDispatcher = $eventDispatcher;
        $this->permalinkFactory = $permalinkFactory;
        $this->writer = $writer;
        $this->generatorManager = $generatorManager;
        $this->formatterManager = $formatterManager;
        $this->converterManager = $converterManager;
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
            if (!$source->isGenerated()) {
                $this->generatorManager->generate($source, $sourceSet);
            }
        }

        foreach ($sourceSet->updatedSources() as $source) {
            $permalink = $this->permalinkFactory->create($source);
            $source->setPermalink($permalink);
            $source->data()->set('url', $permalink->relativeUrlPath());
        }

        foreach ($sourceSet->updatedSources() as $source) {
            $this->converterManager->convertSource($source);
        }

        foreach ($sourceSet->updatedSources() as $source) {
            if ($source->canBeFormatted()) {
                $source->setFormattedContent($this->formatterManager->formatSourcePage($source));
            } else {
                $source->setFormattedContent($source->content());
            }
        }

        $found = false;

        foreach ($sourceSet->updatedSources() as $source) {
            if ($source->isGenerator()) {
                continue;
            }

            if (!$found) {
                print "Detected new or updated files\n";
                $found = true;
            }

            $this->writer->write(new SourceOutput($source));

            print " + {$source->sourceId()}\n";
        }

        $this->eventDispatcher->dispatch(self::EVENT_AFTER_RUN, new SourceSetEvent($sourceSet));
    }
}
