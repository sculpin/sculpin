<?php

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
        FilterInterface $filter,
        MapInterface $map,
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

    public function provideData()
    {
        return $this->collection;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Sculpin::EVENT_BEFORE_RUN => array(
                array('beforeRun', 0),
                array('beforeRunPost', -100),
            ),
            Sculpin::EVENT_AFTER_CONVERT => 'afterConvert',
        );
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
        if (isset($this->collection[$sourceId])) {
            $item = $this->collection[$sourceId];
            $item->setBlocks($this->formatterManager->formatSourceBlocks($convertEvent->source()));
        }
    }
}
