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

namespace Sculpin\Contrib\ProxySourceCollection;

use Doctrine\Common\Inflector\Inflector;
use Sculpin\Core\DataProvider\DataProviderInterface;
use Sculpin\Core\Event\ConvertEvent;
use Sculpin\Core\Event\SourceSetEvent;
use Sculpin\Core\Formatter\FormatterManager;
use Sculpin\Core\Sculpin;
use Sculpin\Core\Source\Filter\FilterInterface;
use Sculpin\Core\Source\Filter\NullFilter;
use Sculpin\Core\Source\Map\MapInterface;
use Sculpin\Core\Source\Map\NullMap;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProxySourceCollectionDataProvider implements DataProviderInterface, EventSubscriberInterface
{
    private $formatterManager;
    private $dataProviderName;
    private $dataSingularName;
    private $collection;
    private $filter;
    private $map;
    private $factory;

    public function __construct(
        FormatterManager $formatterManager,
        $dataProviderName,
        $dataSingularName = null,
        ProxySourceCollection $collection = null,
        FilterInterface $filter = null,
        MapInterface $map = null,
        ProxySourceItemFactoryInterface $factory = null
    ) {
        $this->formatterManager = $formatterManager;
        $this->dataProviderName = $dataProviderName;
        $this->dataSingularName = $dataSingularName ?: Inflector::singularize($dataProviderName);
        $this->collection = $collection ?: new ProxySourceCollection;
        $this->filter = $filter ?: new NullFilter;
        $this->map = $map ?: new NullMap;
        $this->factory = $factory ?: new SimpleProxySourceItemFactory;
    }

    public function provideData(): array
    {
        return iterator_to_array($this->collection);
    }

    public static function getSubscribedEvents()
    {
        return [
            Sculpin::EVENT_BEFORE_RUN => [
                ['beforeRun', 0],
                ['beforeRunPost', -100],
            ],
            Sculpin::EVENT_AFTER_CONVERT => 'afterConvert',
        ];
    }

    public function beforeRun(SourceSetEvent $sourceSetEvent)
    {
        foreach ($sourceSetEvent->updatedSources() as $source) {
            if ($source->isGenerated()) {
                // We want to skip generated sources in case someone is
                // doing something like a redirect where virtual sources are
                // created like a redirect plugin.
                //
                // NOTE: This means that a generator cannot create proxy
                // source collection items. This could be limiting in the
                // future...
                continue;
            }

            if ($this->filter->match($source)) {
                // Skip hidden files.
                if (0 === strpos($source->filename(), '.')) {
                    $source->setShouldBeSkipped();

                    continue;
                }

                // Skip file types that cannot be parsed into blocks.
                // Files without an extension fall into this category
                // because the formatter looks for specific extensions.
                // Files without a newline after the YAML front matter
                // also fall into this category.
                if (!$source->canBeFormatted()) {
                    echo 'Skipping empty or unknown file: ' . $source->relativePathname() . PHP_EOL;

                    $source->setShouldBeSkipped();

                    continue;
                }

                $this->map->process($source);
                $this->collection[$source->sourceId()] = $this->factory->createProxySourceItem($source);
            }
        }
        $foundAtLeastOne = false;

        foreach ($sourceSetEvent->allSources() as $source) {
            if ($this->filter->match($source)) {
                $foundAtLeastOne = true;
                break;
            }
        }

        if (!$foundAtLeastOne) {
            echo 'Didn\'t find at least one of this type : ' . $this->dataProviderName . PHP_EOL;
        }

        $this->collection->init();
    }

    public function beforeRunPost(SourceSetEvent $sourceSetEvent)
    {
        $anItemHasChanged = false;
        foreach ($this->collection as $item) {
            if ($item->hasChanged()) {
                $anItemHasChanged = true;
                $this->collection->init();
                break;
            }
        }
        if ($anItemHasChanged) {
            foreach ($sourceSetEvent->allSources() as $source) {
                if ($source->data()->get('use') && in_array($this->dataProviderName, $source->data()->get('use'))) {
                    $source->forceReprocess();
                }
            }
        }
        foreach ($this->collection as $item) {
            $item->data()->set('next_'.$this->dataSingularName, $item->nextItem());
            $item->data()->set('previous_'.$this->dataSingularName, $item->previousItem());
        }
    }

    public function afterConvert(ConvertEvent $convertEvent)
    {
        $sourceId = $convertEvent->source()->sourceId();

        if (!isset($this->collection[$sourceId])) {
            return;
        }

        $item = $this->collection[$sourceId];

        $item->setBlocks($this->formatterManager->formatSourceBlocks($convertEvent->source()));
    }
}
