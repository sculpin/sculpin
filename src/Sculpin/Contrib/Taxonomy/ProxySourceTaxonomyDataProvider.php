<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Contrib\Taxonomy;

use Sculpin\Core\DataProvider\DataProviderInterface;
use Sculpin\Core\DataProvider\DataProviderManager;
use Sculpin\Core\Event\SourceSetEvent;
use Sculpin\Core\Sculpin;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProxySourceTaxonomyDataProvider implements DataProviderInterface, EventSubscriberInterface
{
    private $taxons = array();
    private $dataProviderManager;
    private $dataProviderName;
    private $taxonomyKey;
    private $taxonomyNormalizedKey;
    private $permalinkStrategyCollection;

    public function __construct(
        DataProviderManager $dataProviderManager,
        $dataProviderName,
        $taxonomyKey,
        $taxonomyNormalizedKey,
        $permalinkStrategyCollection
    ) {
        $this->dataProviderManager = $dataProviderManager;
        $this->dataProviderName = $dataProviderName;
        $this->taxonomyKey = $taxonomyKey;
        $this->taxonomyNormalizedKey = $taxonomyNormalizedKey;
        $this->permalinkStrategyCollection = $permalinkStrategyCollection;
    }

    public function provideData()
    {
        return $this->taxons;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Sculpin::EVENT_BEFORE_RUN => 'beforeRun',
        );
    }

    public function beforeRun(SourceSetEvent $sourceSetEvent)
    {
        $taxons = array();
        $dataProvider = $this->dataProviderManager->dataProvider($this->dataProviderName);

        foreach ($dataProvider->provideData() as $item) {
            if ($itemTaxons = $item->data()->get($this->taxonomyKey)) {
                $normalizedItemTaxons = array();
                $normalizedItemTaxonNormalized[] = array();
                foreach ((array) $itemTaxons as $itemTaxon) {
                    $normalizedItemTaxon = trim($itemTaxon);
                    $taxons[$normalizedItemTaxon][] = $item;
                    $normalizedItemTaxons[] = $normalizedItemTaxon;
                    $normalizedItemTaxonNormalized[$normalizedItemTaxon] = $this->permalinkStrategyCollection->process($normalizedItemTaxon);;
                }
                $item->data()->set($this->taxonomyKey, $normalizedItemTaxons);
                $item->data()->set($this->taxonomyNormalizedKey, $normalizedItemTaxonNormalized);
            }
        }

        $this->taxons = $taxons;
    }
}
