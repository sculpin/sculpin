<?php

namespace Sculpin\Contrib\ProxySourceCollection;

use Sculpin\Core\DataProvider\DataProviderInterface;
use Sculpin\Core\Event\ConvertEvent;
use Sculpin\Core\Event\SourceSetEvent;
use Sculpin\Core\Formatter\FormatterManager;
use Sculpin\Core\Sculpin;
use Sculpin\Core\Source\Filter\FilterInterface;
use Sculpin\Core\Source\Map\MapInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProxySourceCollectionDataProvider implements DataProviderInterface, EventSubscriberInterface
{
    private $formatterManager;
    private $dataProviderName;
    private $collection;
    private $filter;
    private $map;
    private $factory;

    public function __construct(
        FormatterManager $formatterManager,
        $dataProviderName,
        ProxySourceCollection $collection = null,
        FilterInterface $filter,
        MapInterface $map,
        ProxySourceItemFactoryInterface $factory = null
    ) {
        $this->formatterManager = $formatterManager;
        $this->dataProviderName = $dataProviderName;
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
            if ($this->filter->match($source)) {
                $this->map->process($source);
                $this->collection[$source->sourceId()] = $this->factory->createProxySourceItem($source);
            }
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
                if ($source->data()->get('use') and in_array($this->dataProviderName, $source->data()->get('use'))) {
                    $source->forceReprocess();
                }
            }
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
