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

namespace Sculpin\Bundle\SculpinBundle\HttpKernel;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
abstract class AbstractKernel extends Kernel
{
    protected $missingSculpinBundles = [];
    protected $outputDir;
    protected $projectDir;
    protected $sourceDir;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $environment, bool $debug, array $overrides = [])
    {
        $this->projectDir = $overrides['projectDir'] ?? null;
        $this->outputDir  = $overrides['outputDir']  ?? null;
        $this->sourceDir  = $overrides['sourceDir']  ?? null;

        if (null !== $this->projectDir) {
            $this->rootDir = $this->projectDir . '/app';
        }

        parent::__construct($environment, $debug);
    }

    /**
     * {@inheritdoc}
     */
    protected function getKernelParameters(): array
    {
        if (null === $this->projectDir) {
            $this->projectDir = dirname($this->rootDir);
        }

        return array_merge(parent::getKernelParameters(), [
            'sculpin.project_dir'         => $this->projectDir,
            'sculpin.output_dir_override' => $this->outputDir,
            'sculpin.source_dir_override' => $this->sourceDir,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles(): array
    {
        $bundles = [
            new \Sculpin\Bundle\StandaloneBundle\SculpinStandaloneBundle,
            new \Sculpin\Bundle\MarkdownBundle\SculpinMarkdownBundle,
            new \Sculpin\Bundle\TextileBundle\SculpinTextileBundle,
            new \Sculpin\Bundle\MarkdownTwigCompatBundle\SculpinMarkdownTwigCompatBundle,
            new \Sculpin\Bundle\PaginationBundle\SculpinPaginationBundle,
            new \Sculpin\Bundle\SculpinBundle\SculpinBundle,
            new \Sculpin\Bundle\ThemeBundle\SculpinThemeBundle,
            new \Sculpin\Bundle\TwigBundle\SculpinTwigBundle,
            new \Sculpin\Bundle\ContentTypesBundle\SculpinContentTypesBundle,
            new \Sculpin\Bundle\PostsBundle\SculpinPostsBundle,
        ];

        foreach ($this->getAdditionalSculpinBundles() as $class) {
            if (class_exists($class)) {
                $bundles[] = new $class();
            } else {
                $this->missingSculpinBundles[] = $class;
            }
        }

        return $bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        // Load defaults.
        $loader->load(__DIR__.'/../Resources/config/kernel.yml');

        if (file_exists($file = $this->rootDir.'/config/sculpin_kernel_'.$this->getEnvironment().'.yml')) {
            $loader->load($file);
        } elseif (file_exists($file = $this->rootDir.'/config/sculpin_kernel.yml')) {
            $loader->load($file);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        if (true === $this->booted) {
            return;
        }

        parent::boot();

        $this->container->compile();
    }

    /**
     * {@inheritdoc}
     */
    protected function buildContainer(): ContainerBuilder
    {
        $container = $this->getContainerBuilder();
        $container->addObjectResource($this);
        $this->prepareContainer($container);

        if (file_exists($this->rootDir.'/config/sculpin_services.yml')) {
            $loader = new YamlFileLoader($container, new FileLocator($this->rootDir.'/config'));
            $loader->load('sculpin_services.yml');
        }

        if (null !== $cont = $this->registerContainerConfiguration($this->getContainerLoader($container))) {
            $container->merge($cont);
        }

        return $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeContainer(): void
    {
        $container = $this->buildContainer();
        $container->set('kernel', $this);
        $this->container = $container;
    }

    /**
     * Get Sculpin bundles that were requested but were not found
     *
     * This can happen if a bundle is requested but has not been required and
     * installed by Composer. Chances are this will lead to a lot of really bad
     * things. This should be checked early by any Console applications to
     * ensure that proper warnings are issued if there are any missing bundles
     * detected.
     *
     * @return array
     */
    public function getMissingSculpinBundles(): array
    {
        return $this->missingSculpinBundles;
    }

    /**
     * Get additional Sculpin bundles to register.
     *
     * @return string[] Fully qualified class names of the bundles to register.
     */
    abstract protected function getAdditionalSculpinBundles(): array;
}
