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
use Sculpin\Core\Io\IoInterface;
use Sculpin\Core\Io\NullIo;
use Sculpin\Core\Output\SourceOutput;
use Sculpin\Core\Output\WriterInterface;
use Sculpin\Core\Permalink\SourcePermalinkFactoryInterface;
use Sculpin\Core\Source\DataSourceInterface;
use Sculpin\Core\Source\SourceSet;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Sculpin.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class Sculpin
{
    const GIT_VERSION = '@git_version@';

    const EVENT_BEFORE_RUN = 'sculpin.core.before_run';
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
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Permalink factory
     *
     * @var SourcePermalinkFactoryInterface
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
     * IO
     *
     * @var IoInterface
     */
    protected $io;

    /**
     * Constructor.
     *
     * @param Configuration                    $siteConfiguration Site Configuration
     * @param EventDispatcherInterface         $eventDispatcher   Event dispatcher
     * @param SourcePermalinkFactoryInterface  $permalinkFactory  Permalink factory
     * @param WriterInterface                  $writer            Writer
     * @param GeneratorManager                 $generatorManager  Generator Manager
     * @param FormatterManager                 $formatterManager  Formatter Manager
     * @param ConverterManager                 $converterManager  Converter Manager
     */
    public function __construct(
        Configuration $siteConfiguration,
        EventDispatcherInterface $eventDispatcher,
        SourcePermalinkFactoryInterface $permalinkFactory,
        WriterInterface $writer,
        GeneratorManager $generatorManager,
        FormatterManager $formatterManager,
        ConverterManager $converterManager
    ) {
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
     * @param IoInterface         $io         IO Interface
     */
    public function run(DataSourceInterface $dataSource, SourceSet $sourceSet, IoInterface $io = null)
    {
        if (null === $io) {
            $io = new NullIo();
        }
        $found = false;
        $startTime = microtime(true);

        $dataSource->refresh($sourceSet);

        $this->eventDispatcher->dispatch(self::EVENT_BEFORE_RUN, new SourceSetEvent($sourceSet));

        if ($updatedSources = array_filter($sourceSet->updatedSources(), function ($source) {
            return !$source->isGenerated();
        })) {
            if (!$found) {
                $io->write('Detected new or updated files');
                $found = true;
            }

            $total = count($updatedSources);

            $io->write('Generating: ', false);
            $io->write('', false);
            $counter = 0;
            $timer = microtime(true);
            foreach ($updatedSources as $source) {
                $this->generatorManager->generate($source, $sourceSet);
                $io->overwrite(sprintf('%3d%%', 100*((++$counter)/$total)), false);
            }
            $io->write(sprintf(' (%d sources / %4.2f seconds)', $total, microtime(true) - $timer));
        }

        foreach ($sourceSet->updatedSources() as $source) {
            $permalink = $this->permalinkFactory->create($source);
            $source->setPermalink($permalink);
            $source->data()->set('url', $permalink->relativeUrlPath());
        }

        if ($updatedSources = $sourceSet->updatedSources()) {
            if (!$found) {
                $io->write('Detected new or updated files');
                $found = true;
            }

            $total = count($updatedSources);

            $io->write('Converting: ', false);
            $io->write('', false);
            $counter = 0;
            $timer = microtime(true);
            foreach ($updatedSources as $source) {
                $this->converterManager->convertSource($source);

                if ($source->canBeFormatted()) {
                    $source->data()->set('blocks', $this->formatterManager->formatSourceBlocks($source));
                }
                $io->overwrite(sprintf('%3d%%', 100*((++$counter)/$total)), false);
            }
            $io->write(sprintf(' (%d sources / %4.2f seconds)', $total, microtime(true) - $timer));
        }

        if ($updatedSources = $sourceSet->updatedSources()) {
            if (!$found) {
                $io->write('Detected new or updated files');
                $found = true;
            }

            $total = count($updatedSources);

            $io->write('Formatting: ', false);
            $io->write('', false);
            $counter = 0;
            $timer = microtime(true);
            foreach ($updatedSources as $source) {
                if ($source->canBeFormatted()) {
                    $source->setFormattedContent($this->formatterManager->formatSourcePage($source));
                } else {
                    $source->setFormattedContent($source->content());
                }
                $io->overwrite(sprintf('%3d%%', 100*((++$counter)/$total)), false);
            }
            $this->eventDispatcher->dispatch(self::EVENT_AFTER_FORMAT, new SourceSetEvent($sourceSet));
            $io->write(sprintf(' (%d sources / %4.2f seconds)', $total, microtime(true) - $timer));
        }

        foreach ($sourceSet->updatedSources() as $source) {
            if ($source->isGenerator() || $source->shouldBeSkipped()) {
                continue;
            }

            $this->writer->write(new SourceOutput($source));

            if ($io->isVerbose()) {
                $io->write(' + ' . $source->sourceId());
            }
        }

        $this->eventDispatcher->dispatch(self::EVENT_AFTER_RUN, new SourceSetEvent($sourceSet));

        if ($found) {
            $io->write(sprintf('Processing completed in %4.2f seconds', microtime(true) - $startTime));
        }
    }
}
