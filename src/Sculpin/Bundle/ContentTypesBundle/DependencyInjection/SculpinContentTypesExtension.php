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

use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
class SculpinContentTypesExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration;
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        // So we can get all available types.
        $container->setParameter(self::generateId('types'), array_keys($config));

        foreach ($config as $type => $setup) {
            if (! $setup['enabled']) {
                // We can skip any types that are not enabled.
                continue;
            }

            // What should use use for the singular name?
            $singularName = isset($setup['singular_name']) ? $setup['singular_name'] : Inflector::singularize($type);

            // How is the type detected?
            $detectionTypes = is_array($setup['type']) ? $setup['type'] : [$setup['type']];

            $itemClassId = self::generateTypesId($type, 'item.class');
            if (! $container->hasParameter($itemClassId)) {
                $container->setParameter($itemClassId, 'Sculpin\Contrib\ProxySourceCollection\ProxySourceItem');
            }

            //
            // Collection sorter
            //

            $collectionSorterId = self::generateTypesId($type, 'collection.sorter');

            if (! $container->hasDefinition($collectionSorterId)) {
                $collectionSorter = new Definition('Sculpin\Contrib\ProxySourceCollection\Sorter\DefaultSorter');
                $container->setDefinition($collectionSorterId, $collectionSorter);
            }

            //
            // Collection service
            //

            $collectionId = self::generateTypesId($type, 'collection');

            $collection = new Definition('Sculpin\Contrib\ProxySourceCollection\ProxySourceCollection');
            $collection->addArgument([]);
            $collection->addArgument(new Reference($collectionSorterId));
            $container->setDefinition($collectionId, $collection);

            // Contains all of our filters.
            $filters = [];

            // Contains all of our "or" filters.
            $orFilters = [];

            if (in_array('path', $detectionTypes)) {
                if (0 == count($setup['path'])) {
                    $setup['path'] = ['_'.$type];
                }

                //
                // Path Filter
                //

                $pathFilterId = self::generateTypesId($type, 'path_filter');

                $pathFilter = new Definition('Sculpin\Core\Source\Filter\AntPathFilter');
                $pathFilter->addArgument($setup['path']);
                $pathFilter->addArgument(new Reference('sculpin.matcher'));
                $container->setDefinition($pathFilterId, $pathFilter);

                $orFilters[] = new Reference($pathFilterId);
            }

            if (in_array('meta', $detectionTypes)) {
                //
                // Meta Filter
                //

                $key = isset($setup['meta_key']) ? $setup['meta_key'] : 'type';
                $value = isset($setup['meta']) ? $setup['meta'] : $singularName;

                $metaFilterId = self::generateTypesId($type, 'meta_filter');

                $metaFilter = new Definition('Sculpin\Core\Source\Filter\MetaFilter');
                $metaFilter->addArgument($key);
                $metaFilter->addArgument($value);
                $container->setDefinition($metaFilterId, $metaFilter);

                $orFilters[] = new Reference($metaFilterId);
            }

            if (count($orFilters) > 0) {
                //
                // "or" Filter
                //

                $orFilterId = self::generateTypesId($type, 'or_filter');
                $orFilter = new Definition('Sculpin\Core\Source\Filter\ChainFilter');
                $orFilter->addArgument($orFilters);
                $orFilter->addArgument(true);
                $container->setDefinition($orFilterId, $orFilter);

                $filters[] = new Reference($orFilterId);
            }

            //
            // Drafts Filter
            //

            $draftsFilterId = self::generateTypesId($type, 'drafts_filter');

            $publishDrafts = $setup['publish_drafts'];

            if (null === $publishDrafts) {
                $publishDrafts = 'prod' !== $container->getParameter('kernel.environment');
            }

            $draftsFilter = new Definition('Sculpin\Core\Source\Filter\DraftsFilter');
            $draftsFilter->addArgument($publishDrafts);
            $container->setDefinition($draftsFilterId, $draftsFilter);

            $filters[] = new Reference($draftsFilterId);

            //
            // Filter
            //

            $filterId = self::generateTypesId($type, 'filter');

            $filter = new Definition('Sculpin\Core\Source\Filter\ChainFilter');
            $filter->addArgument($filters);
            $container->setDefinition($filterId, $filter);

            //
            // Default Data Map
            //

            $defaultDataMapId = self::generateTypesId($type, 'default_data_map');
            $defaultDataMap = new Definition('Sculpin\Core\Source\Map\DefaultDataMap');
            $defaultDataMap->addArgument([
                'layout' => isset($setup['layout']) ? $setup['layout'] : $singularName,
                'permalink' => isset($setup['permalink']) ? $setup['permalink'] : 'none',
            ]);
            $defaultDataMap->addTag(self::generateTypesId($type, 'map'));
            $container->setDefinition($defaultDataMapId, $defaultDataMap);

            //
            // Calculated Date From Filename Map
            //

            $calculatedDateFromFilenameMapId = self::generateTypesId($type, 'calculated_date_from_filename_map');
            $calculatedDateFromFilenameMap = new Definition('Sculpin\Core\Source\Map\CalculatedDateFromFilenameMap');
            $calculatedDateFromFilenameMap->addTag(self::generateTypesId($type, 'map'));
            $container->setDefinition($calculatedDateFromFilenameMapId, $calculatedDateFromFilenameMap);

            //
            // Drafts Map
            //

            $draftsMapId = self::generateTypesId($type, 'drafts_map');
            $draftsMap = new Definition('Sculpin\Core\Source\Map\DraftsMap');
            $draftsMap->addTag(self::generateTypesId($type, 'map'));
            $container->setDefinition($draftsMapId, $draftsMap);

            //
            // Map
            //

            $mapId = self::generateTypesId($type, 'map');
            $map = new Definition('Sculpin\Core\Source\Map\ChainMap');
            $container->setDefinition($mapId, $map);

            //
            // Item Factory
            //

            $factoryId = self::generateTypesId($type, 'item_factory');
            $factory = new Definition('Sculpin\Contrib\ProxySourceCollection\SimpleProxySourceItemFactory');
            $factory->addArgument(self::generatePlaceholder($itemClassId));
            $container->setDefinition($factoryId, $factory);

            //
            // Data Provider
            //

            $dataProviderId = self::generateTypesId($type, 'data_provider');
            $dataProvider = new Definition('Sculpin\Contrib\ProxySourceCollection\ProxySourceCollectionDataProvider');
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

                $taxon = Inflector::singularize($taxonomyName);

                $taxonomyDataProviderName = $type.'_'.$taxonomyName;
                $taxonomyIndexGeneratorName = $type.'_'.$taxon.'_index';

                $reversedName = $taxon.'_'.$type;

                $taxonomyDataProviderId = self::generateTypesId($type, $taxonomyName.'_data_provider');
                $taxonomyDataProvider = new Definition('Sculpin\Contrib\Taxonomy\ProxySourceTaxonomyDataProvider');
                $taxonomyDataProvider->addArgument(new Reference('sculpin.data_provider_manager'));
                $taxonomyDataProvider->addArgument($type);
                $taxonomyDataProvider->addArgument($taxonomyName);
                $taxonomyDataProvider->addTag('kernel.event_subscriber');
                $taxonomyDataProvider->addTag('sculpin.data_provider', ['alias' => $taxonomyDataProviderName]);
                $container->setDefinition($taxonomyDataProviderId, $taxonomyDataProvider->setPublic(true));

                $taxonomyIndexGeneratorId = self::generateTypesId($type, $taxonomyName.'_index_generator');
                $taxonomyIndexGenerator = new Definition('Sculpin\Contrib\Taxonomy\ProxySourceTaxonomyIndexGenerator');
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

    private static function generatePlaceholder(string $value): string
    {
        return '%'.$value.'%';
    }

    private static function generateId(string $value): string
    {
        return implode('.', ['sculpin_content_types', $value]);
    }

    private static function generateTypesId(string $type, string $value)
    {
        return implode('.', ['sculpin_content_types.types', $type, $value]);
    }
}
