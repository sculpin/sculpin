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

namespace Sculpin\Contrib\Taxonomy;

use Sculpin\Core\DataProvider\DataProviderInterface;
use Sculpin\Core\DataProvider\DataProviderManager;
use Sculpin\Core\Event\SourceSetEvent;
use Sculpin\Core\Sculpin;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProxySourceTaxonomyDataProvider implements DataProviderInterface, EventSubscriberInterface
{
    private $taxons = [];
    private $dataProviderManager;
    private $dataProviderName;
    private $taxonomyKey;

    public function __construct(
        DataProviderManager $dataProviderManager,
        $dataProviderName,
        $taxonomyKey
    ) {
        $this->dataProviderManager = $dataProviderManager;
        $this->dataProviderName = $dataProviderName;
        $this->taxonomyKey = $taxonomyKey;
    }

    public function provideData(): array
    {
        return $this->taxons;
    }

    public static function getSubscribedEvents()
    {
        return [
            Sculpin::EVENT_BEFORE_RUN => 'beforeRun',
        ];
    }

    public function beforeRun(SourceSetEvent $sourceSetEvent)
    {
        $taxons = [];
        $dataProvider = $this->dataProviderManager->dataProvider($this->dataProviderName);

        foreach ($dataProvider->provideData() as $item) {
            if ($itemTaxons = $item->data()->get($this->taxonomyKey)) {
                $normalizedItemTaxons = [];
                foreach ((array) $itemTaxons as $itemTaxon) {
                    $normalizedItemTaxon = trim($itemTaxon);
                    $taxons[$normalizedItemTaxon][] = $item;
                    $normalizedItemTaxons[] = $normalizedItemTaxon;
                }
                $item->data()->set($this->taxonomyKey, $normalizedItemTaxons);
            }
        }

        $this->taxons = $taxons;
    }
}
