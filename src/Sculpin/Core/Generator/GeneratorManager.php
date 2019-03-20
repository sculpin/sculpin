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

namespace Sculpin\Core\Generator;

use Dflydev\DotAccessConfiguration\Configuration;
use Sculpin\Core\DataProvider\DataProviderManager;
use Sculpin\Core\Source\SourceInterface;
use Sculpin\Core\Source\SourceSet;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Generator Manager.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class GeneratorManager
{
    /**
     * Event Dispatcher
     *
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Site Configuration
     *
     * @var Configuration
     */
    protected $siteConfiguration;

    /**
     * Data Provider Manager
     *
     * @var DataProviderManager
     */
    protected $dataProviderManager;

    /**
     * Generators
     *
     * @var array
     */
    protected $generators = [];

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher     Event Dispatcher
     * @param Configuration            $siteConfiguration   Site Configuration
     * @param DataProviderManager      $dataProviderManager Data Provider Manager
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        Configuration $siteConfiguration,
        DataProviderManager $dataProviderManager = null
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->siteConfiguration = $siteConfiguration;
        $this->dataProviderManager = $dataProviderManager;
    }

    /**
     * Register generator
     *
     * @param string             $name      Name
     * @param GeneratorInterface $generator Generator
     */
    public function registerGenerator($name, GeneratorInterface $generator)
    {
        $this->generators[$name] = $generator;
    }

    /**
     * Generate
     *
     * @param  SourceInterface           $source    Source
     * @param  SourceSet                 $sourceSet Source set
     * @throws \InvalidArgumentException
     */
    public function generate(SourceInterface $source, SourceSet $sourceSet): void
    {
        $data = $source->data();

        $generators = [];
        $isGenerator = $source->isGenerator();
        if ($generatorNames = $data->get('generator')) {
            if (!$isGenerator) {
                $source->setIsGenerator();
            }

            $generatorNames = (array) $generatorNames;

            foreach ($generatorNames as $generatorName) {
                if (!isset($this->generators[$generatorName])) {
                    throw new \InvalidArgumentException(sprintf(
                        "Requested generator '%s' could not be found in %s; was it registered?",
                        $generatorName,
                        $source->relativePathname()
                    ));
                }

                $generators[] = $this->generators[$generatorName];
            }
        } else {
            if ($isGenerator) {
                $source->setIsNotGenerator();
            }
        }

        $targetSources = [$source];

        foreach ($generators as $generator) {
            $newTargetSources = [];
            foreach ($targetSources as $targetSource) {
                foreach ((array) $generator->generate($targetSource) as $generatedSource) {
                    $generatedSource->setIsGenerated();
                    $newTargetSources[] = $generatedSource;
                }
            }
            $targetSources = $newTargetSources;
        }

        foreach ($targetSources as $generatedSource) {
            $sourceSet->mergeSource($generatedSource);
        }
    }

    /**
     * Set Data Provider Manager.
     *
     * NOTE: This is a hack because Symfony DiC cannot handle passing Data Provider
     * Manager via constructor injection as some data providers might also rely
     * on formatter. Hurray for circular dependencies. :(
     *
     * @param DataProviderManager $dataProviderManager Data Provider Manager
     */
    public function setDataProviderManager(DataProviderManager $dataProviderManager = null)
    {
        $this->dataProviderManager = $dataProviderManager;
    }
}
