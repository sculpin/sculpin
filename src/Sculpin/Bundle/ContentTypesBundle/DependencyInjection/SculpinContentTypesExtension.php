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

namespace Sculpin\Bundle\ContentTypesBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Sculpin\Contrib\ProxySourceCollection\ProxySourceItem;
use Sculpin\Contrib\ProxySourceCollection\Sorter\DefaultSorter;
use Sculpin\Contrib\ProxySourceCollection\ProxySourceCollection;
use Sculpin\Core\Source\Filter\AntPathFilter;
use Sculpin\Core\Source\Filter\MetaFilter;
use Sculpin\Core\Source\Filter\ChainFilter;
use Sculpin\Core\Source\Filter\DraftsFilter;
use Sculpin\Core\Source\Map\DefaultDataMap;
use Sculpin\Core\Source\Map\CalculatedDateFromFilenameMap;
use Sculpin\Core\Source\Map\DraftsMap;
use Sculpin\Core\Source\Map\ChainMap;
use Sculpin\Contrib\ProxySourceCollection\SimpleProxySourceItemFactory;
use Sculpin\Contrib\ProxySourceCollection\ProxySourceCollectionDataProvider;
use Sculpin\Contrib\Taxonomy\PermalinkStrategyCollection;
use Sculpin\Contrib\Taxonomy\PermalinkStrategyCollectionFactory;
use Sculpin\Contrib\Taxonomy\ProxySourceTaxonomyDataProvider;
use Sculpin\Contrib\Taxonomy\ProxySourceTaxonomyIndexGenerator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\String\Inflector\EnglishInflector;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
class SculpinContentTypesExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration;
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        // So we can get all available types.
        $container->setParameter($this->generateId('types'), array_keys($config));

        foreach ($config as $type => $setup) {
            if (! $setup['enabled']) {
                // We can skip any types that are not enabled.
                continue;
            }

            // What should be used for the singular name?
            $singularName = $setup['singular_name'] ?? (new EnglishInflector())->singularize($type)[0];

            // How is the type detected?
            $detectionTypes = is_array($setup['type']) ? $setup['type'] : [$setup['type']];

            $itemClassId = $this->generateTypesId($type, 'item.class');
            if (! $container->hasParameter($itemClassId)) {
                $container->setParameter($itemClassId, ProxySourceItem::class);
            }

            //
            // Collection sorter
            //

            $collectionSorterId = $this->generateTypesId($type, 'collection.sorter');

            if (! $container->hasDefinition($collectionSorterId)) {
                $collectionSorter = new Definition(DefaultSorter::class);
                $container->setDefinition($collectionSorterId, $collectionSorter);
            }

            //
            // Collection service
            //

            $collectionId = $this->generateTypesId($type, 'collection');

            $collection = new Definition(ProxySourceCollection::class);
            $collection->addArgument([]);
            $collection->addArgument(new Reference($collectionSorterId));
            $container->setDefinition($collectionId, $collection);

            // Contains all of our filters.
            $filters = [];

            // Contains all of our "or" filters.
            $orFilters = [];

            if (in_array('path', $detectionTypes)) {
                if (0 === count($setup['path'])) {
                    $setup['path'] = ['_'.$type];
                }

                //
                // Path Filter
                //

                $pathFilterId = $this->generateTypesId($type, 'path_filter');

                $pathFilter = new Definition(AntPathFilter::class);
                $pathFilter->addArgument($setup['path']);
                $pathFilter->addArgument(new Reference('sculpin.matcher'));
                $container->setDefinition($pathFilterId, $pathFilter);

                $orFilters[] = new Reference($pathFilterId);
            }

            if (in_array('meta', $detectionTypes)) {
                //
                // Meta Filter
                //

                $key = $setup['meta_key'] ?? 'type';
                $value = $setup['meta'] ?? $singularName;

                $metaFilterId = $this->generateTypesId($type, 'meta_filter');

                $metaFilter = new Definition(MetaFilter::class);
                $metaFilter->addArgument($key);
                $metaFilter->addArgument($value);
                $container->setDefinition($metaFilterId, $metaFilter);

                $orFilters[] = new Reference($metaFilterId);
            }

            if ($orFilters !== []) {
                //
                // "or" Filter
                //

                $orFilterId = $this->generateTypesId($type, 'or_filter');
                $orFilter = new Definition(ChainFilter::class);
                $orFilter->addArgument($orFilters);
                $orFilter->addArgument(true);
                $container->setDefinition($orFilterId, $orFilter);

                $filters[] = new Reference($orFilterId);
            }

            //
            // Drafts Filter
            //

            $draftsFilterId = $this->generateTypesId($type, 'drafts_filter');

            $publishDrafts = $setup['publish_drafts'];

            if (null === $publishDrafts) {
                $publishDrafts = 'prod' !== $container->getParameter('kernel.environment');
            }

            $draftsFilter = new Definition(DraftsFilter::class);
            $draftsFilter->addArgument($publishDrafts);
            $container->setDefinition($draftsFilterId, $draftsFilter);

            $filters[] = new Reference($draftsFilterId);

            //
            // Filter
            //

            $filterId = $this->generateTypesId($type, 'filter');

            $filter = new Definition(ChainFilter::class);
            $filter->addArgument($filters);
            $container->setDefinition($filterId, $filter);

            //
            // Default Data Map
            //

            $defaultDataMapId = $this->generateTypesId($type, 'default_data_map');
            $defaultDataMap = new Definition(DefaultDataMap::class);
            $defaultDataMap->addArgument([
                'layout' => $setup['layout'] ?? $singularName,
                'permalink' => $setup['permalink'] ?? 'none',
            ]);
            $defaultDataMap->addTag($this->generateTypesId($type, 'map'));
            $container->setDefinition($defaultDataMapId, $defaultDataMap);

            //
            // Calculated Date From Filename Map
            //

            $calculatedDateFromFilenameMapId = $this->generateTypesId($type, 'calculated_date_from_filename_map');
            $calculatedDateFromFilenameMap = new Definition(CalculatedDateFromFilenameMap::class);
            $calculatedDateFromFilenameMap->addTag($this->generateTypesId($type, 'map'));
            $container->setDefinition($calculatedDateFromFilenameMapId, $calculatedDateFromFilenameMap);

            //
            // Drafts Map
            //

            $draftsMapId = $this->generateTypesId($type, 'drafts_map');
            $draftsMap = new Definition(DraftsMap::class);
            $draftsMap->addTag($this->generateTypesId($type, 'map'));
            $container->setDefinition($draftsMapId, $draftsMap);

            //
            // Map
            //

            $mapId = $this->generateTypesId($type, 'map');
            $map = new Definition(ChainMap::class);
            $container->setDefinition($mapId, $map);

            //
            // Item Factory
            //

            $factoryId = $this->generateTypesId($type, 'item_factory');
            $factory = new Definition(SimpleProxySourceItemFactory::class);
            $factory->addArgument($this->generatePlaceholder($itemClassId));
            $container->setDefinition($factoryId, $factory);

            //
            // Data Provider
            //

            $dataProviderId = $this->generateTypesId($type, 'data_provider');
            $dataProvider = new Definition(ProxySourceCollectionDataProvider::class);
            $dataProvider->addArgument(new Reference('sculpin.formatter_manager'));
            $dataProvider->addArgument($type);
            $dataProvider->addArgument($singularName);
            $dataProvider->addArgument(new Reference($collectionId));
            $dataProvider->addArgument(new Reference($filterId));
            $dataProvider->addArgument(new Reference($mapId));
            $dataProvider->addArgument(new Reference($factoryId));
            $dataProvider->addTag('sculpin.data_provider', ['alias' => $type]);
            $dataProvider->addTag('kernel.event_subscriber');
            $container->setDefinition($dataProviderId, $dataProvider->setPublic(true));

            foreach ($setup['taxonomies'] as $taxonomy) {
                $taxonomyName = is_string($taxonomy) ? $taxonomy : $taxonomy['name'];
                $permalinkStrategyId = $taxonomyName.'_permalink_strategy';
                $permalinkStrategies = new Definition('Sculpin\Contrib\Taxonomy\PermalinkStrategyCollection');
                $permalinkStrategies->setFactory([
                    'Sculpin\Contrib\Taxonomy\PermalinkStrategyCollectionFactory', 'create'
                ]);
                $permalinkStrategies->addArgument($taxonomy);
                $container->setDefinition($permalinkStrategyId, $permalinkStrategies);

                $taxon = (new EnglishInflector())->singularize($taxonomyName)[0];

                $taxonomyDataProviderName = $type.'_'.$taxonomyName;
                $taxonomyIndexGeneratorName = $type.'_'.$taxon.'_index';

                $reversedName = $taxon.'_'.$type;

                $taxonomyDataProviderId = $this->generateTypesId($type, $taxonomyName.'_data_provider');
                $taxonomyDataProvider = new Definition(ProxySourceTaxonomyDataProvider::class);
                $taxonomyDataProvider->addArgument(new Reference('sculpin.data_provider_manager'));
                $taxonomyDataProvider->addArgument($type);
                $taxonomyDataProvider->addArgument($taxonomyName);
                $taxonomyDataProvider->addTag('kernel.event_subscriber');
                $taxonomyDataProvider->addTag('sculpin.data_provider', ['alias' => $taxonomyDataProviderName]);
                $container->setDefinition($taxonomyDataProviderId, $taxonomyDataProvider->setPublic(true));

                $taxonomyIndexGeneratorId = $this->generateTypesId($type, $taxonomyName.'_index_generator');
                $taxonomyIndexGenerator = new Definition(ProxySourceTaxonomyIndexGenerator::class);
                $taxonomyIndexGenerator->addArgument(new Reference('sculpin.data_provider_manager'));
                $taxonomyIndexGenerator->addArgument($taxonomyDataProviderName);
                $taxonomyIndexGenerator->addArgument($taxon);
                $taxonomyIndexGenerator->addArgument($reversedName);
                $taxonomyIndexGenerator->addArgument($permalinkStrategies);
                $taxonomyIndexGenerator->addTag('sculpin.generator', ['alias' => $taxonomyIndexGeneratorName]);
                $container->setDefinition($taxonomyIndexGeneratorId, $taxonomyIndexGenerator->setPublic(true));
            }
        }
    }

    private function generatePlaceholder(string $value): string
    {
        return '%'.$value.'%';
    }

    private function generateId(string $value): string
    {
        return implode('.', ['sculpin_content_types', $value]);
    }

    private function generateTypesId(string $type, string $value): string
    {
        return implode('.', ['sculpin_content_types.types', $type, $value]);
    }
}
