<?php

namespace Sculpin\Bundle\ContentTypesBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class FilterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $types = $container->getParameter('sculpin_content_types.types');

        $filters = array();

        // Validate tags and group by `detection` attribute.
        foreach ($container->findTaggedServiceIds('sculpin.content_type.filter') as $id => $tagAttributes) {

            if (!isset($tagAttributes[0]['type'])) {
                throw new \InvalidArgumentException(sprintf('Service "%s" must define the "type" attribute on "%s" tags.',
                  $id, 'sculpin.content_type.filter'));
            }

            if (!in_array($tagAttributes[0]['type'], $types)) {
                throw new \InvalidArgumentException(sprintf('Service "%s" type attribute on "%s" tags is not an active content type.',
                  $id, 'sculpin.content_type.filter'));
            }

            $type = $tagAttributes[0]['type'];
            $subchain = false;

            if (isset($tagAttributes[0]['or']) && $tagAttributes[0]['or']) {
                $subchain = true;
            }

            $filters[$type][$subchain][] = $id;
        }


        $types = array_keys($filters);

        foreach ($types as $type) {
            $definition = $container->getDefinition('sculpin_content_types.filter.chain.'. $type);

            // Multiple detectors.
            if (isset($filters[$type][TRUE]) && count($filters[$type][TRUE]) > 1) {
                $id = self::createOrChainFilter($container, $type, $filters[$type][TRUE]);
                $definition->addMethodCall('addFilter', array(new Reference($id)));
            }

            // One detector.
            if (isset($filters[$type][TRUE]) && count($filters[$type][TRUE]) === 1) {
                foreach ($filters[$type][TRUE] as $id) {
                    $definition->addMethodCall('addFilter', array(new Reference($id)));
                }
            }

            // All the other detectors.
            if (isset($filters[$type][FALSE])) {
                foreach ($filters[$type][FALSE] as $id) {
                    $definition->addMethodCall('addFilter', array(new Reference($id)));
                }
            }
        }
    }

    private static function createOrChainFilter(ContainerBuilder $container, $type, array $ids = array())
    {
        $id = 'sculpin_content_types.filter.or.'. $type;
        $definition = new DefinitionDecorator('sculpin_content_types.filter.or');
        $definition->addArgument(array(array(), true));
        foreach ($ids as $id) {
            $definition->addMethodCall('addFilter', array(new Reference($id)));
        }
        $container->setDefinition($id, $definition);
        return $id;
    }
}
