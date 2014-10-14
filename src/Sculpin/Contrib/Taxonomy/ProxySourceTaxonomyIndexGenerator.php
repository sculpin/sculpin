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

use Sculpin\Core\DataProvider\DataProviderManager;
use Sculpin\Core\Generator\GeneratorInterface;
use Sculpin\Core\Source\SourceInterface;

class ProxySourceTaxonomyIndexGenerator implements GeneratorInterface
{
    private $dataProviderManager;
    private $dataProviderName;
    private $injectedTaxonKey;
    private $injectedTaxonItemsKey;

    public function __construct(
        DataProviderManager $dataProviderManager,
        $dataProviderName,
        $injectedTaxonKey,
        $injectedTaxonItemsKey
    ) {
        $this->dataProviderManager = $dataProviderManager;
        $this->dataProviderName = $dataProviderName; // post_tags
        $this->injectedTaxonKey = $injectedTaxonKey; // tag
        $this->injectedTaxonItemsKey = $injectedTaxonItemsKey; // tagged_posts
    }

    public function generate(SourceInterface $source)
    {
        $dataProvider = $this->dataProviderManager->dataProvider($this->dataProviderName);
        $taxons = $dataProvider->provideData();

        $generatedSources = array();
        foreach ($taxons as $taxon => $items) {
            $generatedSource = $source->duplicate(
                $source->sourceId().':'.$this->injectedTaxonKey.'='.$taxon
            );

            $permalink = $source->data()->get('permalink') ?: $source->relativePathname();
            $basename = basename($permalink);

            $permalink = dirname($permalink);

            if (preg_match('/^(.+?)\.(.+)$/', $basename, $matches)) {
                $permalink = $permalink.'/'.$taxon.'/index.html';
            } else {
                // not sure what case this is?
            }

            if (0 === strpos($permalink, './')) {
                $permalink = substr($permalink, 2);
            }

            if ($permalink) {
                // not sure if this is ever going to happen?
                $generatedSource->data()->set('permalink', $permalink);
            }

            $generatedSource->data()->set($this->injectedTaxonKey, $taxon);
            $generatedSource->data()->set($this->injectedTaxonItemsKey, $items);


            //
            // TODO: REMOVE THIS
            //
            // This is needed for BC purposes. This should be removed
            // eventually and existing markup should be updated.
            //

            switch ($this->injectedTaxonItemsKey) {
                case 'tag_posts':
                    $generatedSource->data()->set('tagged_posts', $items);
                    break;
                case 'category_posts':
                    $generatedSource->data()->set('categoried_posts', $items);
                    break;
            }

            $generatedSources[] = $generatedSource;
        }

        return $generatedSources;
    }
}
