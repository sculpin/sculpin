<?php

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
use Sculpin\Core\Sculpin;
use Sculpin\Core\Source\SourceInterface;
use Sculpin\Core\Source\SourceSet;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
     * @var EventDispatcher
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
    protected $generators = array();

    /**
     * Constructor.
     *
     * @param EventDispatcher     $eventDispatcher     Event Dispatcher
     * @param Configuration       $siteConfiguration   Site Configuration
     * @param DataProviderManager $dataProviderManager Data Provider Manager
     */
    public function __construct(EventDispatcher $eventDispatcher, Configuration $siteConfiguration, DataProviderManager $dataProviderManager = null)
    {
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
     * @param SourceInterface $source    Source
     * @param SourceSet       $sourceSet Source set
     *
     * @return string
     */
    public function generate(SourceInterface $source, SourceSet $sourceSet)
    {
        $data = $source->data();

        $isGenerator = $source->isGenerator();
        if ($generatorName = $data->get('generator')) {
            if (!$isGenerator) {
                $source->setIsGenerator();
            }

            if (!isset($this->generators[$generatorName])) {
                throw new \InvalidArgumentException("Requested generator '$generatorName' could not be found; was it registered?");
            }

            $generator = $this->generators[$generatorName];
        } else {
            if ($isGenerator) {
                $source->setIsNotGenerator();
            }

            return;
        }

        foreach ((array) $generator->generate($source) as $generatedSource) {
            $generatedSource->setIsGenerated();
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
