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

use Sculpin\Core\DataProvider\DataProviderManager;
use Sculpin\Core\Generator\GeneratorInterface;
use Sculpin\Core\Source\SourceInterface;

readonly class ProxySourceTaxonomyIndexGenerator implements GeneratorInterface
{
    public function __construct(
        private DataProviderManager $dataProviderManager,
        private string $dataProviderName, // post_tags
        private string $injectedTaxonKey, // tag
        private string $injectedTaxonItemsKey, // tagged_posts
        private PermalinkStrategyCollection $permalinkStrategyCollection
    ) {
    }

    public function generate(SourceInterface $source): array
    {
        $dataProvider = $this->dataProviderManager->dataProvider($this->dataProviderName);
        $taxons = $dataProvider->provideData();

        $generatedSources = [];
        foreach ($taxons as $taxon => $items) {
            $generatedSource = $source->duplicate(
                $source->sourceId() . ':' . $this->injectedTaxonKey . '=' . $taxon
            );

            $permalink = $source->data()->get('permalink') ?: $source->relativePathname();
            $basename = basename((string) $permalink);

            $permalink = dirname((string) $permalink);

            $indexType = null;

            if (preg_match('/^(.+?)\.(.+)$/', $basename, $matches)) {
                $urlTaxon = $this->permalinkStrategyCollection->process($taxon);
                $indexType = $matches[2];
                $suffix = in_array($indexType, ['xml', 'rss', 'json']) ? '.'.$indexType : '/';
                $permalink = $permalink.'/'.$urlTaxon.$suffix;
            }

            if (str_starts_with($permalink, './')) {
                $permalink = substr($permalink, 2);
            }

            if (!str_starts_with($permalink, '/')) {
                $permalink = '/'.$permalink;
            }

            // not sure if this is ever going to happen?
            $generatedSource->data()->set('permalink', $permalink);

            $generatedSource->data()->set($this->injectedTaxonKey, $taxon);
            $generatedSource->data()->set($this->injectedTaxonItemsKey, $items);

            if ($indexType) {
                foreach ($items as $item) {
                    $key = $this->injectedTaxonKey.'_'.$indexType.'_index_permalinks';
                    $taxonIndexPermalinks = $item->data()->get($key) ?: [];

                    $taxonIndexPermalinks[$taxon] = $permalink;

                    $item->data()->set($key, $taxonIndexPermalinks);
                }
            }

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
