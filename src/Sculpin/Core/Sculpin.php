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
use Sculpin\Core\Source\SourceInterface;
use Sculpin\Core\Source\SourceSet;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Main entry point to interact with the Sculpin system.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
final class Sculpin
{
    public const EVENT_BEFORE_RUN = 'sculpin.core.before_run';
    public const EVENT_AFTER_RUN = 'sculpin.core.after_run';

    public const EVENT_AFTER_GENERATE = 'sculpin.core.after_generate';

    public const EVENT_BEFORE_CONVERT = 'sculpin.core.before_convert';
    public const EVENT_AFTER_CONVERT = 'sculpin.core.after_convert';

    public const EVENT_BEFORE_FORMAT = 'sculpin.core.before_format';
    public const EVENT_AFTER_FORMAT = 'sculpin.core.after_format';

    /**
     * @var Configuration
     */
    private $siteConfiguration;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var SourcePermalinkFactoryInterface
     */
    private $permalinkFactory;

    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @var GeneratorManager
     */
    private $generatorManager;

    /**
     * @var FormatterManager
     */
    private $formatterManager;

    /**
     * @var ConverterManager
     */
    private $converterManager;

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

    public function run(DataSourceInterface $dataSource, SourceSet $sourceSet, IoInterface $io = null)
    {
        if (null === $io) {
            $io = new NullIo();
        }
        $found = false;
        $startTime = microtime(true);

        $dataSource->refresh($sourceSet);

        $this->eventDispatcher->dispatch(self::EVENT_BEFORE_RUN, new SourceSetEvent($sourceSet));

        if ($updatedSources = array_filter($sourceSet->updatedSources(), function (SourceInterface $source) {
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
            $source->data()->set('relative_pathname', $source->relativePathname());
            $source->data()->set('filename', $source->filename());
        }

        $this->eventDispatcher->dispatch(self::EVENT_AFTER_GENERATE, new SourceSetEvent($sourceSet));

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
