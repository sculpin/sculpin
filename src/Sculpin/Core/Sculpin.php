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

use Sculpin\Core\Configuration\Configuration;
use Sculpin\Core\Converter\ConverterInterface;
use Sculpin\Core\Converter\SourceConverterContext;
use Sculpin\Core\Event\SourceSetEvent;
use Sculpin\Core\Event\ConvertSourceEvent;
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
    const EVENT_BEFORE_RUN = 'sculpin.core.beforeRun';
    const EVENT_BEFORE_RUN_AGAIN = 'sculpin.core.beforeRunAgain';
    const EVENT_AFTER_RUN = 'sculpin.core.afterRun';

    const EVENT_BEFORE_CONVERT = 'sculpin.core.beforeConvert';
    const EVENT_AFTER_CONVERT = 'sculpin.core.afterConvert';

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
            $this->convert($source);
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
    protected function convert(SourceInterface $source)
    {
        $converters = $source->data()->get('converters');
        if (!$converters || !is_array($converters)) {
            return;
        }

        foreach ($converters as $converter) {
            $this->eventDispatcher->dispatch(self::EVENT_BEFORE_CONVERT, new ConvertSourceEvent($source, $converter, $this->configuration->defaultFormatter()));
            $this->converter($converter)->convert(new SourceConverterContext($source));
            $this->eventDispatcher->dispatch(self::EVENT_AFTER_CONVERT, new ConvertSourceEvent($source, $converter, $this->configuration->defaultFormatter()));
        }
    }
}
