<?php

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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Sculpin Content Types Extension.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class SculpinContentTypesExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        // So we can get all available types.
        $container->setParameter('sculpin_content_types.types', array_keys($config));

        foreach ($config as $type => $setup) {
            if (!$this->isConfigEnabled($container, $setup)) {
                // We can skip any types that are not enabled.
                continue;
            }

            $collectionId = self::collectionFactory($container, $type);

            // How is the type detected?
            $detectionTypes = is_array($setup['type']) ? $setup['type'] : array($setup['type']);

            if (in_array('path', $detectionTypes)) {
                $pathFilterFactoryId = self::pathFilterFactory($container, $type, $setup['path']);
            }

            if (in_array('meta', $detectionTypes)) {
                $metaFilterFactoryId = self::metaFilterFactory($container, $type, $setup['meta_key'], $setup['meta']);
            }

            $draftFilterFactoryId = self::draftFilterFactory($container, $type, $setup['publish_drafts']);
            $filterId = self::filterFactory($container, $type);
            $dataMapId = self::defaultDataMapFactory($container, $type, $setup['layout'], $setup['permalink']);
            $mapId = self::filterMapFactory($container, $type);
            $factoryId = self::itemFactory($container, $type);

            $dataProviderId = self::dataProviderFactory($container, $type, $setup['singular_name'], $collectionId, $filterId, $mapId, $factoryId);
            foreach ($setup['taxonomies'] as $taxonomy) {
                self::taxonomyFactory($container, $type, $taxonomy);
            }
        }
    }

    /**
     * Create collection sorter and collection service.
     *
     * @param ContainerBuilder $container
     * @param $type
     * @return string
     */
    private static function collectionFactory(ContainerBuilder $container, $type)
    {
        $collectionSorterId = 'sculpin_content_types.collection.sorter.'.$type;

        if (!$container->hasDefinition($collectionSorterId)) {
            $collectionSorter = new DefinitionDecorator('sculpin_content_types.collection.sorter');
            $container->setDefinition($collectionSorterId, $collectionSorter);
        }

        $collectionId = 'sculpin_content_types.collection.'.$type;

        $collection = new DefinitionDecorator('sculpin_content_types.collection');
        $collection->addArgument(array());
        $collection->addArgument(new Reference($collectionSorterId));
        $container->setDefinition($collectionId, $collection);

        return $collectionId;
    }

    /**
     * Create Path Filter service.
     *
     * @param ContainerBuilder $container
     * @param $type
     * @param $path
     * @return string
     */
    private static function pathFilterFactory(ContainerBuilder $container, $type, $path)
    {
        $id = 'sculpin_content_types.filter.path.'.$type;

        $definition = new DefinitionDecorator('sculpin_content_types.filter.path');
        $definition->addArgument($path);
        $definition->addArgument(new Reference('sculpin.matcher'));
        $definition->addTag('sculpin.content_type.filter', array('type' => $type, 'or' => true));
        $container->setDefinition($id, $definition);

        return $id;
    }

    /**
     * Create Meta Filter service.
     *
     * @param ContainerBuilder $container
     * @param $type
     * @param $key
     * @param $value
     * @return string
     */
    private static function metaFilterFactory(ContainerBuilder $container, $type, $key, $value)
    {
        $id = 'sculpin_content_types.filter.meta.'.$type;

        $definition = new DefinitionDecorator('sculpin_content_types.filter.meta');
        $definition->addArgument($key);
        $definition->addArgument($value);
        $definition->addTag('sculpin.content_type.filter', array('type' => $type, 'or' => true));
        $container->setDefinition($id, $definition);

        return $id;
    }

    /**
     * Create drafts filter.
     *
     * @param ContainerBuilder $container
     * @param $type
     * @param $publishDrafts
     * @return string
     */
    private static function draftFilterFactory(ContainerBuilder $container, $type, $publishDrafts)
    {
        $id = 'sculpin_content_types.filter.drafts.'.$type;

        if (null === $publishDrafts) {
            $publishDrafts = 'prod' !== $container->getParameter('kernel.environment');
        }

        $definition = new DefinitionDecorator('sculpin_content_types.filter.drafts');
        $definition->addArgument($publishDrafts);
        $definition->addTag('sculpin.content_type.filter', array('type' => $type));
        $container->setDefinition($id, $definition);

        return $id;
    }

    /**
     * Create filter service.
     *
     * @param ContainerBuilder $container
     * @param $type
     * @return string
     */
    private static function filterFactory(ContainerBuilder $container, $type)
    {
        $id = 'sculpin_content_types.filter.chain.'.$type;

        $definition = new DefinitionDecorator('sculpin_content_types.filter.chain');
        $container->setDefinition($id, $definition);

        return $id;
    }

    /**
     * Create Default Data Map service.
     *
     * @param ContainerBuilder $container
     * @param $type
     * @param $layout
     * @param $permalink
     * @return string
     */
    private static function defaultDataMapFactory(ContainerBuilder $container, $type, $layout, $permalink)
    {
        $id = 'sculpin_content_types.map.default_data.'.$type;
        $definition = new DefinitionDecorator('sculpin_content_types.map.default_data');
        $definition->addArgument(array(
          'layout' => $layout,
          'permalink' => $permalink,
        ));
        $definition->addTag('sculpin.content_type.map', array('type' => $type));
        $container->setDefinition($id, $definition);

        return $id;
    }

    /**
     * Create map services for file names and drafts.
     *
     * @param ContainerBuilder $container
     * @param $type
     * @return string
     */
    private static function filterMapFactory(ContainerBuilder $container, $type)
    {
        //
        // Calculated Date From Filename Map
        //

        $calculatedDateFromFilenameMapId = 'sculpin_content_types.map.calculated_date_from_filename.'.$type;
        $calculatedDateFromFilenameMap = new DefinitionDecorator('sculpin_content_types.map.calculated_date_from_filename');
        $calculatedDateFromFilenameMap->addTag('sculpin.content_type.map', array('type' => $type));
        $container->setDefinition($calculatedDateFromFilenameMapId, $calculatedDateFromFilenameMap);

        //
        // Drafts Map
        //

        $draftsMapId = 'sculpin_content_types.map.drafts.'.$type;
        $draftsMap = new DefinitionDecorator('sculpin_content_types.map.drafts');
        $draftsMap->addTag('sculpin.content_type.map', array('type' => $type));
        $container->setDefinition($draftsMapId, $draftsMap);

        //
        // Map
        //

        $mapId = 'sculpin_content_types.map.chain.'.$type;
        $map = new DefinitionDecorator('sculpin_content_types.map.chain');
        $container->setDefinition($mapId, $map);

        return $mapId;
    }

    /**
     * Create item factory service.
     *
     * @param ContainerBuilder $container
     * @param $type
     * @return string
     */
    private static function itemFactory(ContainerBuilder $container, $type)
    {
        $itemClassId = 'sculpin_content_types.item.'.$type.'.class';
        if (!$container->hasParameter($itemClassId)) {
            $container->setParameter($itemClassId, $container->getParameter('sculpin_content_types.item.class'));
        }

        $id = 'sculpin_content_types.item_factory.'.$type;
        $definition = new DefinitionDecorator('sculpin_content_types.item_factory');
        $definition->addArgument('%'.$itemClassId.'%');
        $container->setDefinition($id, $definition);

        return $id;
    }

    /**
     * Create data provider service.
     *
     * @param ContainerBuilder $container
     * @param $type
     * @param $singularName
     * @param $collectionId
     * @param $filterId
     * @param $mapId
     * @param $factoryId
     * @return string
     */
    private static function dataProviderFactory(ContainerBuilder $container, $type, $singularName, $collectionId, $filterId, $mapId, $factoryId)
    {
        $id = 'sculpin_content_types.data_provider.'.$type;
        $definition = new DefinitionDecorator('sculpin_content_types.data_provider');
        $definition->addArgument($type);
        $definition->addArgument($singularName);
        $definition->addArgument(new Reference($collectionId));
        $definition->addArgument(new Reference($filterId));
        $definition->addArgument(new Reference($mapId));
        $definition->addArgument(new Reference($factoryId));
        $definition->addTag('sculpin.data_provider', array('alias' => $type));
        $definition->addTag('kernel.event_subscriber');
        $container->setDefinition($id, $definition);

        return $id;
    }

    /**
     * Creates permalink strategy collection service.
     *
     * @param ContainerBuilder $container
     * @param $type
     * @param $taxonomy
     * @return string
     */
    private static function permalinkStrategyCollectionFactory(ContainerBuilder $container, $type, $taxonomy)
    {
        $id = 'sculpin_content_types.permalink_strategy_collection.'.$type;
        $definition = new DefinitionDecorator('sculpin_content_types.permalink_strategy_collection');
        foreach ($taxonomy['strategies'] as $strategy) {
            $definition->addMethodCall('push', array(new Definition($strategy)));
        }
        $container->setDefinition($id, $definition);

        return $id;
    }

    /**
     * Creates taxonomy data provider and index generator services.
     *
     * @param ContainerBuilder $container
     * @param $type
     * @param $taxonomy
     * @return array
     */
    private static function taxonomyFactory(ContainerBuilder $container, $type, $taxonomy)
    {
        $permalinkStrategiesId = self::permalinkStrategyCollectionFactory($container, $type, $taxonomy);
        $taxonomyName = $taxonomy['name'];
        $taxon = Inflector::singularize($taxonomyName);

        $taxonomyDataProviderName = $type.'_'.$taxonomyName;
        $taxonomyDataProviderId = 'sculpin_content_types.'.$taxonomyName.'_data_provider.'.$type;
        $taxonomyDataProvider = new DefinitionDecorator('sculpin_content_types.taxonomy_data_provider');
        $taxonomyDataProvider->addArgument($type);
        $taxonomyDataProvider->addArgument($taxonomyName);
        $taxonomyDataProvider->addTag('kernel.event_subscriber');
        $taxonomyDataProvider->addTag('sculpin.data_provider', array('alias' => $taxonomyDataProviderName));
        $container->setDefinition($taxonomyDataProviderId, $taxonomyDataProvider);

        $reversedName = $taxon.'_'.$type;
        $taxonomyIndexGeneratorName = $type.'_'.$taxon.'_index';
        $taxonomyIndexGeneratorId = 'sculpin_content_types.'.$taxonomyName.'_index_generator.'.$type;
        $taxonomyIndexGenerator = new DefinitionDecorator('sculpin_content_types.taxonomy_index_generator');
        $taxonomyIndexGenerator->addArgument($taxonomyDataProviderName);
        $taxonomyIndexGenerator->addArgument($taxon);
        $taxonomyIndexGenerator->addArgument($reversedName);
        $taxonomyIndexGenerator->addArgument(new Reference($permalinkStrategiesId));
        $taxonomyIndexGenerator->addTag('sculpin.generator', array('alias' => $taxonomyIndexGeneratorName));
        $container->setDefinition($taxonomyIndexGeneratorId, $taxonomyIndexGenerator);

        return array($taxonomyDataProviderId, $taxonomyIndexGeneratorId);
    }
}
